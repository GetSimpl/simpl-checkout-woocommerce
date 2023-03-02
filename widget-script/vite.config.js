import { defineConfig, loadEnv } from "vite";
import path from "path";

export default {
  build: {
    minify: "terser",
    lib: {
      formats: ["iife"],
      entry: path.resolve(__dirname, "src/main.js"),
      name: "iife",
      fileName: "simpl-checkout-widget-v2",
    },
  },
  define: {
    SIMPL_WIDGET_VERSION: JSON.stringify(process.env.npm_package_version),
  },
};
