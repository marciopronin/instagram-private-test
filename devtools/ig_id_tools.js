// ID TOOLS

var IG_ID_CLASS = 'X.0LF'
var RANDOM = function() {};

function uuidv4() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
      (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

function _randomHex(len) {
    var hex = '0123456789abcdef';
    var output = '';
    for (var i = 0; i < len; ++i) {
        output += hex.charAt(Math.floor(Math.random() * hex.length));
    }
    return output;
}

function spoofAndroidID(android_id) {
    if (android_id == RANDOM) {
        android_id = _randomHex(16);
    } else if (android_id !== null) {
        android_id = String(android_id).toLowerCase();
        if (! android_id.match(/^[0-9a-f]{16}$/)) {
            throw new Error("Invalid Android ID value");
        }
    }
    var ss = Java.use("android.provider.Settings$Secure");
    ss.getString.overload("android.content.ContentResolver", "java.lang.String").implementation = function(context, param) {
        if (param == ss.ANDROID_ID.value) {
            return android_id;
        } else {
            return this.getString(context, param);
        }
    }
}

/*
    This class is responsible for setting UUID and X-Ig-Device-Id.

    This UUID is randomly generated and stored in the device.
    The first part of the UUID is replaced by the integer 663.

    String default = "6d387f45-afa7-4375-ac7a-50919837fafe";
    String[] splitted = "6d387f45-afa7-4375-ac7a-50919837fafe".split("-");
    if(splitted.length >= 2) {
        String random = Integer.toHexString(new Random().nextInt(15));
        long tsm = System.currentTimeMillis();
        s3 = "6d387f45-afa7-4375-ac7a-50919837fafe".replaceFirst(splitted[1], 02I.string_concat(random, Long.toHexString((tsm - 0LF.A01) / 1000L + 0x663L)));
    }

    The first file that is checked for UUID is /mnt/sdcard/.profig.os, if this is empty or doesn't contain any UUID it will check INSTALLATION file from private
    Instagram folder. If any of these exists or aren't valid UUIDs, then it is generated as per the above commented code. The generated UUID will be written in
    INSTALLATION file.
*/
Java.perform(function() {
  var id_class = Java.use(IG_ID_CLASS);
  var get_uuid = id_class.get_uuid.overload('android.content.Context');
  get_uuid.implementation = function(context) {
    var ret = this.get_uuid(context);
    console.log('[get_uuid] original: '+ret);
    var uuid = uuidv4()
    console.log('[get_uuid] spoofed: '+uuid);
    return uuid;
  };
});

/*
    This is the android ID, it is static and it might change upon factory reset.

    Can be checked by:
        - settings get secure android_id
        - grep android_id /data/system/users/0/settings_secure.xml
        - grep android_id /data/system/users/0/settings_ssaid.xml
*/
Java.perform(function() {
    var id_class = Java.use(IG_ID_CLASS);
    var get_android_id = id_class.get_android_id.overload('android.content.Context');
    get_android_id.implementation = function(context) {
      var device_id = Java.use('android.provider.Settings$Secure').getString(context, 'android_id')
      console.log('[get_android_id] original: '+device_id);
      var ret = spoofAndroidID(RANDOM)
      console.log('[get_android_id] spoofed: '+ret);
      return ret;
    };
  });