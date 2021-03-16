# Drupal Developers Recipe

## Prerequisite

- Node.js 12.x

## How to Use on your local environment

```sh
git clone git@github.com:annai-labs/drupal-developers-recipe.git
cd drupal-developers-recipe
npm install
npx marp --server docs
open http://localhost:8080
```

## A short example how to deploy on the Google App Engine

```sh
gcloud config set project {YOUR_PROJECT_ID}
gcloud app create --region=asia-northeast1
gcloud app deploy --project={YOUR_PROJECT_ID}
```

### Can I use any authentication before access?

Yes, you can use [Identify-aware Proxy](https://cloud.google.com/iap/docs/concepts-overview).
