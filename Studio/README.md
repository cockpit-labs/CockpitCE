# Cockpit Studio web module

Sencha ExtJS application dedicated to Cockpit Studio application.

See [ExtJS framework](https://www.sencha.com/products/extjs) or [Community Edition](https://www.sencha.com/products/extjs/communityedition/).

See Also [ExtJS documentation](https://docs.sencha.com/extjs) and [Cmd documentation](https://docs.sencha.com/cmd).

ExtJS Version used during development : `7.3.0`.

## Prerequisites

- Sencha Cmd 7+ installed

## Sencha commands

### Download ExtJs
```
sencha app upgrade -ext@7.3.0.55
```
=> Now you should have ExtJS framework stored in Sencha Cmd directory: `/WhereSenchaCmdIsInstalled/repo/extract/ext/7.3.0.55`

### Install ExtJs
```
sencha app upgrade /WhereSenchaCmdIsInstalled/repo/extract/ext/7.3.0.55
```

### Build 
```
sencha app build
```
=> Application is built in `/dist` directory

Then remove script src to make application load Keycloak before launch:
```
sed -i 's/ src="microloader.js"//g' dist/index.html
```
Note: for MacOS, you may need to add `''` as suffix for `-i` option:
```
sed -i '' 's/ src="microloader.js"//g' dist/index.html
```