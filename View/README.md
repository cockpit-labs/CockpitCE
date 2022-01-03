Build View module:
```shell
cd View
docker run --rm --name yarn -u $(id -u) -v "$PWD":/usr/src/app -v /tmp:/.cache -w /usr/src/app node:lts-slim yarn install
docker run --rm --name yarn -u $(id -u) -v "$PWD":/usr/src/app -v /tmp:/.cache -w /usr/src/app node:lts-slim yarn build
```
