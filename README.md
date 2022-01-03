# Cockpit Community Edition


## Build Cockpit

Build View module:
```shell
cd View
docker run --rm --name yarn -v "$PWD":/usr/src/app -w /usr/src/app node:lts-alpine sh -c "apk --update --no-cache --virtual build-dependencies add python3 make g++ && yarn install"
docker run --rm --name yarn -u $(id -u) -v "$PWD":/usr/src/app -w /usr/src/app node:lts-alpine yarn build
```

Build Admin module:
```shell
cd Admin
docker run --rm -v "$PWD":/code munenari/sencha-cmd /bin/bash -c "sencha app upgrade -ext@7.3.0.55; sencha app upgrade /opt/sencha/repo/extract/ext/7.3.0.55; sencha app build; sed -i 's/ src=\"microloader.js\"//g' dist/index.html"
```

Build Studio module:
```shell
cd Studio
docker run --rm -v "$PWD":/code munenari/sencha-cmd /bin/bash -c "sencha app upgrade -ext@7.3.0.55; sencha app upgrade /opt/sencha/repo/extract/ext/7.3.0.55; sencha app build; sed -i 's/ src=\"microloader.js\"//g' dist/index.html"
```


Build Cockpit container:

```shell
docker-compose build
```

## Start Cockpit

Run container:

```shell
docker-compose up -d
```


Create MySQL DB and initialize Cockpit
```shell
docker exec -ti cockpit_app ./createCockpitDB.sh
docker exec -ti cockpit_app bin/console cockpit:core:init --drop
```

At this time, a superuser with the same password as keycloak admin (see [.env file](.env)) can connect to Cockpit [View](http://localhost), [Admin](http://localhost/admin) and [Studio](http://localhost/studio).

