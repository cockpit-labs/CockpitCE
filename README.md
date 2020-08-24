# Cockpit Community Edition

Cockpit is a mobile solution for checklist, audit and process management. 

It helps you to send information to your team, control your processes and provides dashboard of your operational execution. Cockpit is used in the retail industry for store audit and management, healthcare for patient medical follow-up and many industries for equipment or process monitoring. Ready-to-use, Cockpit includes : users & groups management, checklists creation, pre-calibrated answer types, scoring and benchmarking as well as many other useful features.

## Features

### Create your checklists

Create/clone/modify questionnaires with dozens of answer types : yes/no, scale, MQC, photos, select, date, numbers...

### Manage users & groups

Import people and organisation or manage your groups, roles & hierarchy as to write checklists, access dashboards and scoring...

### Analyse your feed-backs

Complete dashboard with benchmarks, scoring, filters, progressions and a lot more.

### Photo gallery

Automatically extract all the pics from your questionnaires to display and filter them in the photo gallery.

## CockpitCE repositories

### Core
This is the [Symfony](https://symfony.com/) core, with [API Platform](https://api-platform.com/) bundle.

[Repo is here](https://github.com/cockpit-labs/Core)

### View
The View module is the main app, used for filling questionnaires, viewing dashboard and so on.

[Repo is here](https://github.com/cockpit-labs/View)

### Admin
The Admin module allows questionnaires and folders creation.

[Repo is here](https://github.com/cockpit-labs/Admin)

## Demo with Docker Compose

To use the `Docker` demo, download the [Docker composer file](Docker/cockpitce.yml), and run the command:

```bash
docker-compose -f cockpitce.yml up
```

This will create a `mysql`container, a `keycloak`container and the `cockpitce` container. Technical users and password can be changed in the docker composer file.

MySQL

      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: keycloak
      MYSQL_USER: keycloak
      MYSQL_PASSWORD: keycloak

Keycloak

      DB_DATABASE: keycloak
      DB_USER: keycloak
      DB_PASSWORD: keycloak
      KEYCLOAK_USER: admin
      KEYCLOAK_PASSWORD: KPa550rd
 
 CockpitCE
 
      DB_USER: cockpitce
      DB_PASSWORD: cockpitce
      DB_DATABASE: cockpitce


Usable app users are:
* `audie.fritsch` a Country Manager  
* `kallie.dibbert` the `Store-22` manager

In this demo, user's password is equal to username.

Once docker containers running, API swagger UI can be used at the url http://localhost:81/core/api

To get a token for a user, use this command (for user audie.fritsch and admin client):
```bash
docker exec -ti demo_cockpitce_1 /app/core/bin/console demo:token:generate --username audie.fritsch --client cockpitadmin
``` 
Or, for user kallie.dibbert with View client:
```bash
docker exec -ti demo_cockpitce_1 /app/core/bin/console demo:token:generate --username kallie.dibbert --client cockpitview
``` 


The CockpitCE View app is running at this url : http://localhost:81, and Keycloak here: http://localhost:8080
