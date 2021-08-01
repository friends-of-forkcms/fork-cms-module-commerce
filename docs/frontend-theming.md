# Frontend/Theming

Currently the module includes the `CommerceDemo` demo theme which is used in the preview url and to develop locally.
It's built using an modern and productive stack:

* [Tailwind CSS](https://tailwindcss.com), providing utility classes to style components.
* [Alpine.js](https://alpinejs.dev), to sprinkle javascript on our Twig templates.
* [Vite](https://vitejs.dev), a lightning fast build tool. Only needs a `vite.config.js` file, less complex than Webpack.
* [Typescript](https://www.typescriptlang.org)

The demo theme uses a few dependencies. We try to avoid jQuery and go for lightweight, vanilla JS libraries. E.g.

* [Algolia Autocomplete.js](https://github.com/algolia/autocomplete) which provides a simple way to create a very powerful search box.
* [Splide](https://splidejs.com/), a lightweight slider and carousel written in pure Javascript without any dependencies.
* [Notyf](https://github.com/caroso1222/notyf), a simple, minimalistic, dependency-free, ~ 3KB Javascript library for toast notifications.
* [Photoswipe](https://photoswipe.com) a vanilla Javascript lightbox/image gallery without dependencies.


## Run Vite dev server with HMR
When we edit our .html.twig, .ts or .css files, we would like to instantly see the changes. Start the frontend dev server with:

```bash
cd src/Frontend/Themes/CommerceDemo
npm run dev
```

This will start up Vite in watch-mode, and also output Typescript errors in the console.
The `vite_entry_link_tags` and `vite_entry_script_tags` Twig tags in `Base.html.twig` will check if the dev server is running and output the appropriate tags.
