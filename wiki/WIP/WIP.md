# Work in Progress


Version: 181.0.0.33.117
Build version: 281579026
Capabilities: 3brTvx0=
Blok ID: b5d5130968d5e0a7db34728659625d1d0acf7726af9b9ba8a7f590dd26e798f1

Headers
========

X-IG-App-Locale
X-IG-Device-Locale
X-IG-Mapped-Locale
X-IG-Prefetch-Request => "foreground" or "background"
X-IG-Low-Data-Mode-Image => true (optinal)
X-IG-Low-Data-Mode-Video => true (optional)
X-IG-App-Startup-Country
X-IG-WWW-Claim => init to 0

X-Bloks-Is-Layout-RTL
X-Bloks-Is-Panorama-Enabled
X-IG-Device-ID
X-IG-Android-ID
X-IG-Timezone-Offset

FOR EU USERS
============

"ig_traffic_routing_universe", false, "is_in_lla_routing_experiment"
"X-IG-EU-DC-ENABLED" => route_to_lla

"ig_traffic_routing_universe", false, "is_in_cr_routing_experiment"
"X-IG-CONCURRENT-ENABLED", "route_to_cr_header"

# TOP priority

Instagram has migrated to a new way to handle challenge/checkpoint based in bloks. It will be updated ASAP!

# Working on..

Improving AI detection with built-in events and batch classifier (nearly done)