setTimeout(function() {
    Interceptor.attach(Module.findExportByName("libliger.so", "_ZN8proxygen15SSLVerification17verifyWithMetricsEbP17x509_store_ctx_stRKNSt6__ndk112basic_stringIcNS3_11char_traitsIcEENS3_9allocatorIcEEEEPNS0_31SSLFailureVerificationCallbacksEPNS0_31SSLSuccessVerificationCallbacksERKNS_15TimeUtilGenericINS3_6chrono12steady_clockEEERNS_10TraceEventE"), {
        onLeave: function(retval) {
            console.log(retval);
            retval.replace(ptr('0x1'));
        }
    });
}, 5000);