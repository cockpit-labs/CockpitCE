# Cockpit Community Edition

Cockpit  is a mobile solution for checklist and audit in the real world.

It helps you to send information to your team, control your processes with checklists and provides dashboard of your operational execution.

Cockpit is used in the retail industry for store audit and management, healthcare for patient medical follow-up and many industries for equipment or process monitoring.

Ready-to-use, cockpit includes : users & groups management, checklist creation, pre-calibrated answer types, scoring and benchmarking as well as many other useful features.

## Features

### Create your checklists

Create/clone/modify questionnaires with dozens of answer types : yes/no, scale, MQC, photos, select, date, numbers...

### Manage users & groups

Import people and organisation or manage your groups, roles & hierarchy as to write checklists, access dashboards and scoring...

### Analyse your feed-backs

Complete dashboard with benchmarks, scoring, filters, progressions and a lot more.

### Photo gallery

Automatically extract all the pics from your questionnaires to display and filter them in the photo gallery.


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

