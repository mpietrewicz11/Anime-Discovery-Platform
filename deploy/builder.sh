#!/bin/bash
V=$1; T=$2
[[ -z $V || -z $T]] && echo "usage: buidler.sh <version> <qa|prod>" && exit 1

BN="adem_v$(V).tar.gz"
[[ $T == "qa" ]] && IP="100.107.182.2" || IP="100.76.15.56"

cat > manifest.json << EOF
{
	"version": "$V",
	"target": "$T",
	"target_ip": "$IP",
	"files": [
		{"src": "webserver", "dest": "var/www/html"},
		{"src": "backend", "dest": /home/mikey/git/IT-490-Project/backend"}
	],
		"commands": [],
		"services": ["apache2", "it490-backend"]
	}
EOF

tar -czf $BN webserver/ backend/ manifest.json
rm manifest.json
scp $BN ae396@100.102.151.59:/opt/bundles/
rm $BN
echo "sent $BN"
