# Preview environments

This module deploys to a preview environment using Github Actions. It uses the [Okteto](https://okteto.com/) Kubernetes platform, which currently offers free Kubernetes deployments up to 10 pods and 5GB storage.

## How the preview deploy works

1. The Github action [`preview.yml`](../.github/workflows/preview.yml) is scheduled and regularly deploys the master branch to Okteto. If a PR is created, it also creates a deploy suffixed with the PR number using the [`preview-pr.yml`](.github/workflows/preview-pr.yml) Github action.
2. The [`okteto-pipeline`](../okteto-pipeline.yml) file describes what should be deployed.
    - It will build the container image using the [`Dockerfile`](../Dockerfile)
        - The Dockerfile defines a stage to build our frontend theme, and a stage that starts from `php:7.4-apache` and installs the needed dependencies. It will also download a Fork CMS project and make sure the module copied to the right directory.
    - It injects the container image tag, app label and deployment name in the Kubernetes files using [Kustomize](https://kustomize.io/), and it deploys the Kubernetes files in the `k8s/` folder to Okteto.
    - The `k8s/` folder defines the web deployment which deploys a single pod with two containers: the PHP app and a MariaDB container. No need for high-availability or persistant changes on a preview environment. If the pod is deleted or rescheduled, the DB resets based on the fixture data.
3. When the container starts up, the `deploy/docker-entrypoint.sh` will execute a few things:
    - Wait until the DB is up and running.
    - Import the `.sql` backup of a fresh Fork CMS install in the database.
    - Create an `app/config/parameters.yml` file with the right variables. We simply configure it to use the ENV vars that are defined in the container.
    - Install a user avatar file to prevent a broken image (this is normally installed via the Fork CMS installer).
    - Install our custom Composer dependencies required by this module
    - Install the Sitemap Fork CMS module
    - Install the Commerce Fork CMS module
    - Apply a `git` patch that makes a few changes to the Core files: adding a bundle to `AppKernel.php`, adding `liip_imagine` presets for the thumbnails, configuring a twig extension for the Fork CMS theme, ... (💡 feel free to suggest a better way to achieve this!)
    - Generate some demo data using Doctrine Fixtures
    - Execute some SQL queries that create a few pages in Fork CMS with the right module and widgets attached to it.
    - Clear cache and ready to go 🚀
4. The apache webserver process starts up
5. When visiting https://fork-cms-module-commerce-demo-jessedobbelaere.cloud.okteto.net/ you should see the deployed website.
