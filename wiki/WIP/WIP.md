# Work in Progress

- Version: Instagram 170.2.0.30.474
- Build: 267925697
- Bloks ID: bfe7510720e920cb359b6fc8e96cfb8323a7127b448ecd0d54dc057e3720e766
- Capabilities: 3brTvx8=

## Manage experiments client

X-IG-EU-DC-ENABLED and X-IG-CONCURRENT-ENABLED must be controlled by two set of experiments:

if ($this->ig->isExperimentEnabled('ig_traffic_routing_universe','is_in_lla_routing_experiment', false)) {
   add X-IG-EU-DC-ENABLED = $this->ig->isExperimentEnabled('ig_traffic_routing_universe','route_to_lla', false)
}

if ($this->ig->isExperimentEnabled('ig_traffic_routing_universe','is_in_cr_routing_experiment', false)) {
   add X-IG-CONCURRENT-ENABLED = $this->ig->isExperimentEnabled('ig_traffic_routing_universe','route_to_cr_header', false)
}


# Working on..

Vanish mode
Improving AI detection with built-in events and batch classifier