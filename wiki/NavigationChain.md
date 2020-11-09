# Navigation chain

**WARNING:** PLEASE READ EVERYTHING! NAVIGATION CHAIN IS CRITICAL!

Since recent Instagram app versions, Instagram has started to use an event called `ig_sessions_chain_update`, this is always used before a `navigation` event and tracks the navigation path (chain) followed by the user. Instagram uses this to verify if the user is using a third party library like the API or if it is a legit user.

Everytime a user does a navigation they check the internal class from the app where it comes and add it to the navigation chain, they use that server side to see if the classes matches, if not, then they can flag/detect at the same moment if the user is using the API. THIS MEANS YOU CAN NO LONGER SET A CUSTOM API VERSION WITHOUT CHANGING CLASSES INFO. YOU SHOULD ALWAYS WAIT FOR THAT INFO TO BE UPDATED.

Navigation chain is only implemented for android users at the moment for obvious reasons. Instagram for iOS is not obfuscated, classes would be always the same, there is no point in adding that on iOS. However in Android, each version is compiled with dexguard and therefore the obfuscation makes the name of every file to be different in each version.