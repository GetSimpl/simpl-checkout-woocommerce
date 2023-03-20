#!/bin/sh

svn checkout https://plugins.svn.wordpress.org/simpl-pay-in-3-for-woocommerce/ svn
cd svn/
rsync -a --exclude 'assets' --exclude 'scripts' --exclude 'README.md' --exclude '.circleci' --exclude '.git' --exclude '.gitignore' ~/pay-in-3-woo-commerce-plugin/ trunk/ --delete
rsync -rc '../pay-in-3-woo-commerce-plugin/assets/' assets/ --delete
mkdir -p tags/$CIRCLE_TAG
scp -r trunk/* tags/$CIRCLE_TAG
svn add . --force
svn status | grep '^\!' | sed 's/! *//' | xargs -I% svn rm %@
svn ci -m "release $CIRCLE_TAG" --username $SVN_USERNAME --password $SVN_PASSWORD
cd ..
rm -Rf svn
