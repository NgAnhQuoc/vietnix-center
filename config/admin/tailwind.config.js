module.exports = {
  important: false,
  mode: "jit",
  purge: false,
  media: false,
  theme: {
    container: {
      center: true,
    },
    extend: {
      boxShadow: {
        default: "0px 4px 10px 3px rgba(0, 0, 0, 0.05)",
        widget: "rgba(1, 1, 1, 0.05) 1px 1px 5px 0px",
        box: "0 1px 2px rgb(0 0 0 / 10%)",
      },
      borderWidth: {
        DEFAULT: "1px",
      },
      borderRadius: {
        DEFAULT: "5px",
      },
      borderColor: {
        DEFAULT: "#E4E6EA",
      },
      colors: {
        brand: "#39A7FE",
        danger: "#dd5145",

        primary: {
          // DEFAULT: "#39A7FE",
          DEFAULT: "#39A7FE",
          700: "#5865f2",
        },
      },
      aspectRatio: {
        "4/3": "4 / 3",
        "1200/630": "1200 / 630",
      },
    },
  },
  plugins: [
    require("preline/plugin"),
    require("tailwindcss-aspect-ratio"),
    require("tailwindcss-children"),
    require("flowbite/plugin"),
    ({ addUtilities }) => {
      const utils = {
        ".center": {
          display: "flex",
          "justify-content": "center",
          "align-items": "center",
        },
        ".text-primary": {
          color: "#38A7FF",
        },
      };
      addUtilities(utils, ["responsive", "hover"]);
    },
  ],
  content: require("fast-glob").sync([
    "node_modules/preline/dist/*.js",
    "./**.php",
    // **/*.php (khong phai **.php): fast-glob coi "**" trong cung mot segment nhu "*",
    // nen dang cu bo sot views/tools/partials/.
    "./views/**/*.php",
  ]),
};
