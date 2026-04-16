#!/bin/bash
V=$1; T=$2
[[ -z $V || -z $T ]] && echo "usage: buidler.sh <version> <qa|prod>" && exit 1

BN="adem_v${V}.tar.gz"
case $T in
	qa-fe) IP="100.116.21.106" ;;
	qa-be) IP="100.107.182.2" ;;
	dev-fe) IP="100.82.248.26" ;;
	dev-be) IP="100.96.224.82" ;;
	prod-fe) IP="100.90.102.9" ;;
	prod-be) IP="100.67.69.11" ;;
	*) echo "unknown target $T" && exit 1 ;;
esac

cat > manifest.json << EOF
{
	"version": "$V",
	"target": "$T",
	"target_ip": "$IP",
	"files": [
		{"src": "webserver", "dest": "/var/www/html"},
		{"src": "backend", "dest": "/home/mikey/git/IT-490-Project/backend"}
	],
		"commands": [],
		"services": ["apache2", "it490-backend"]
	}
EOF

tar -czf $BN webserver/ backend/ manifest.json
rm manifest.json
scp $BN ae396@100.64.95.105:/opt/bundles/
rm $BN
echo "sent $BN"
