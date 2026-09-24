module.exports = {
  important: true,
  mode: "jit",
  purge: false,
  media: false,
  theme: {
    container: {
      center: true,
      padding: "16px",
    },
    extend: {
      width: {
        72: "18rem",
        84: "21rem",
        96: "24rem",
      },
      boxShadow: {
        // default: "0px 5px 35px rgba(0, 0, 0, 0.08)",
        default: "0px 4px 10px 3px rgba(0, 0, 0, 0.05)",
        widget: "rgba(1, 1, 1, 0.05) 1px 1px 5px 0px",
        box: "0 1px 2px rgb(0 0 0 / 10%)",
      },
      borderRadius: {
        default: "10px",
      },
      backgroundColor: {
        primary: "#E0EEFF",
        danger: "#FFDCE0",
      },
      borderColor: {
        DEFAULT: "#E4E6EA",
      },
      colors: {
        link: {
          default: "#499DFF",
          hover: "#63b3ed",
        },
        brand: "#38a7ff",
        "blue-dark": "#274E7D",

        title: "#132239", //"#081F32",
        primary: "#408DE4", // #2D8DFE
        // secondary: "#6e798c",
        secondary: "#768192",
        tertiary: "#b4bcc3",

        heading: "#013a52", // #013a52

        // heading: "#303640",

        label: "#5A6375",
        placeholder: "#A5ADBA",

        success: "#02A84E",
        warning: "#FFBF00",
        danger: "#FE172D",
        gray: {
          1: "#333333",
          2: "#4F4F4F",
          3: "#828282",
          4: "#BDBDBD",
          6: "#F2F2F2",
        },
      },
    },
    aspectRatio: {
      none: 0,
      square: [1, 1], // or 1 / 1, or simply 1
      "16/9": [16, 9], // or 16 / 9
      "4/3": [4, 3], // or 4 / 3
      "1200/630": [1200, 630], // or 21 / 9
      banner: [1440, 600],
    },
  },
  plugins: [
    require("preline/plugin"),
    require("tailwindcss-aspect-ratio"),
    require("tailwindcss-children"),
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
  content: require('fast-glob').sync(["./views/archive/**.php", "./views/components/**.php", "./views/components/**/**.php", "./views/widgets/vnx-theme/**.php", "./views/widgets/vnx-theme/**/**.php", "./vnx-theme-page/**.php"]),
}