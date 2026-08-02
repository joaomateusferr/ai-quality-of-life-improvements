#!/bin/bash

#https://kaustavmukherjee-66179.medium.com/how-to-uninstall-and-reinstall-ollama-4253ba07e3f9

set -e

MODEL="qwen3.5:9b"

if [ $(id -u) -ne 0 ]; then #this screipt equire root privileges (root id is 0)
    echo 'No root privileges detected!'
    echo 'Please, run this script as root'
    exit 1
fi

if ! command -v curl >/dev/null 2>&1; then
    apt-get update
    apt-get install -y curl
fi

curl -fsSL https://ollama.com/install.sh | sh

echo "Configuring external access..."

mkdir -p /etc/systemd/system/ollama.service.d

tee /etc/systemd/system/ollama.service.d/override.conf >/dev/null <<EOF
[Service]
Environment="OLLAMA_HOST=0.0.0.0"
EOF

mkdir -p /usr/share/ollama
chown -R ollama:ollama /usr/share/ollama

systemctl daemon-reload
systemctl enable ollama
systemctl restart ollama

echo "Waiting for API to start..."

sleep 5

echo "Downloading model ${MODEL}"

ollama pull "${MODEL}"

echo "Installed models:"

ollama list

IP=$(hostname -I | awk '{print $1}')

cat <<EOF

Installation complete!

Local API:
    http://127.0.0.1:11434

Network API:
    http://${IP}:11434

Test:

curl http://${IP}:11434/api/generate \\
  -H "Content-Type: application/json" \\
  -d '{
        "model":"${MODEL}",
        "prompt":"Hi!",
        "stream":false
      }'

EOF