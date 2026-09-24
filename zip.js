const fs = require("fs");
const archiver = require("archiver");
const archive = archiver("zip");
var pjson = require("./package.json");

const inputPath = ".";

const outputPath = "./dist";

const exclude = [
    "node_modules/**",
    "assets/scss/**",
    "dist/**",
    "yarn.lock",
    "package.json",
    "tailwind.config.js",
    "webpack.mix.js",
    "zip.js",
    "build/mix-manifest.json",
    "config/**",
    ".gitignore"
];

if (!fs.existsSync(outputPath)) {
    fs.mkdirSync(outputPath, {
        recursive: true,
    });
}

const output = fs.createWriteStream(
    `${outputPath}/vietnix-center.zip`
);

output.on("close", function() {
    console.log("Zip plugin complete");
});

archive.on("error", function(err) {
    throw err;
});

archive.glob("**/*", {
    cwd: inputPath,
    ignore: exclude,
});

archive.pipe(output);

archive.finalize();