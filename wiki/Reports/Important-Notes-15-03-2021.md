# Report 15/03/2021

Instagram has recently done a massive ban on flagged/suspicious IPs.

### What is a flagged IP?

It is an IP that has been used for abusing Facebook/Instagram actions. Legit users have mobile and domestic IPs, so all proxy IPs proceeding from other sources have a higher chance to be flagged.

If 100 accounts are being used in a single IP, and 20 of those accounts become flagged, their anti-bot AI will proceed to flag other accounts in the same IP if they have common patterns (aka flows). This also applies for IPs using the same subnet.

You should drop an IP/subnet is more than 30% of the accounts are flagged in that IP.

### What if they have flagged the IP and havent done actions?

It is likely the IP you are using comees from a server that hasnt good reputation (reputational scoring) and Facebook just blindly proceeds to flag the IP. Some flags just comes in form of verifications, this means you can easily work with your account using the checkpoint script.

### What type of IP is recommended?

Only mobile and domestic IPs. You should use the same location for the proxy as the account.

### How to prevent to be detected as a bot?

Randomization. You need to provide similar behaviours like an average user would do. Timings are important, if you do things faster than a human, their AI can easily put your account flagged using different classifiers.

With all actions, you MUST always use events. Events are used as a positive feedback for Instagram, the more events we are capable to provide to them, the better. The idea is to look like a real user.

### Ranking up

Once you have everything ready with events and emulating human patterns, you are ready to slowly rank up the actions. Accounts tends to have less restrictions as they grow old, but never do things that an average user wouldnt be able to do.

### Examples with full event implementation

In the example section you will find many examples with all the events implemented.