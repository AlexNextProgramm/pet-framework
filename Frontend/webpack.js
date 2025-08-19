const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const webpack  = require("webpack");

class Setting {
  js = "";
  css = "";
  clear = ['view/assets/**'];
  img = "view/assets/img";
  font = "view/assets/fonts";
  dist = "";
  template = "./head.php";
    path;
    fs;
    pages = {
        html: [],
        entry: { root: "./root.tsx" }
    };
  IsImages = null;
  dir = '/';
  classes = {
      // MiniCssExtractPlugin,
      // CssMinimizerPlugin,
      // TerserWebpackPlugin,
      // HTMLWebpackPlugin,
      // CleanWebpackPlugin,
      // CopyWebpackPlugin
    };
    isDev = false;
    isProd = false;
    constructor() { 
      
    }
    init() {
        this.getPage();
        this.getIsImages();
    }

  getENV() {
          const ENV = {};
          try {
          const fileENV = this.fs.readFileSync(this.path.resolve(this.dir, ".env"), { encoding: "utf8", flag: 'r' });

          fileENV.split("\n").forEach((row) => {
            const line = row.trim();

            // Пропускаем пустые строки и комментарии
            if (!line || line.startsWith("#")) return;

            // Разбиваем по первому =
            const index = line.indexOf("=");
            if (index === -1) return;

            const key = line.substring(0, index).trim();
            let value = line.substring(index + 1).trim();

            // Убираем кавычки вокруг значения (одинарные или двойные)
            if ((value.startsWith('"') && value.endsWith('"')) || (value.startsWith("'") && value.endsWith("'"))) {
              value = value.slice(1, -1);
            }

            // Убираем возможные символы переноса строки и возврата каретки внутри значения
            value = value.replace(/[\r\n]/g, "").trim();

            ENV[key] = value;
          });
            ['JS', 'CSS', 'IMG', 'FONT', 'TEMPLATE', 'DIST'].forEach((key) => { 
              if (ENV[key]) {
                this[key.toLowerCase()] = ENV[key];
                console.log("env get param "+key+": " + ENV[key])
              }
            })
          if (ENV['CLEAR']) {
                console.log("env get param CLEAR: " +ENV['CLEAR'])
                this.clear = ENV['CLEAR'].split("||");
          }
        } catch (err) {
          console.error("Error reading .env file:", err);
        }
    }

    getPage() {
        const dir = this.path.resolve(this.dir, this.dist + "/view/page")
        const page = this.fs.readdirSync(dir);
        page.forEach((fileAndDir) => {
            const file = dir + '/' + fileAndDir;
            if (this.fs.statSync(file).isDirectory()) {
              const name = fileAndDir
              const entrydir = this.path.join(this.dir, "src/page", `${name}.ts`);
              let ext = this.fs.existsSync(entrydir) ? 'ts' : 'tsx';
                this.pages.entry[name] = `./page/${name}.${ext}`;
                this.pages.html.push({
                    filename: './view/page/' + `${name}/head.php`,
                    template: this.template,
                    entry: this.path.join(this.dir, "src/page", `${name}.${ext}`),
                    chunks: ['root', name ],
                    minify: { collapseWhitespace: this.isProd }
                })
          }
        })
    }

    getIsImages() { 
        const imagesDir = this.path.resolve(this.dir, "src/images")
        if (this.fs.existsSync(imagesDir)) { 
            const files = this.fs.readdirSync(imagesDir);
            if (files.length !== 0) { 
            this.IsImages = new this.classes.CopyWebpackPlugin({
                patterns: [
                {
                    context: this.path.resolve(this.dir, "src/images"),
                    from: "*.*",
                    to: this.img,
                },
                ],
            });
            }
        }
    }
    optimization() { 
        const config = {
            splitChunks: {
              chunks: "all",
            },
          };
        
          if (this.isProd) {
            config.minimizer = [new this.classes.CssMinimizerPlugin(), new this.classes.TerserWebpackPlugin()];
          }
        
          return config;
    }
    cssLoaders(extra = null) { 
        const loaders = [
            this.classes.MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                importLoaders: 1,
              },
            }
          ];
        
          if (extra) loaders.push(extra);
          
          return loaders;
    }
    babelOptions(preset) {
        const opts = {
            presets: ["@babel/preset-env"],
          };
        
          if (preset) opts.presets.push(preset);
        
          return opts;
    }

    plugins() {
        const basePlugins = [
            ...this.pages.html.map(page => new this.classes.HTMLWebpackPlugin(page)),
            
            new CleanWebpackPlugin({
              cleanOnceBeforeBuildPatterns:this.clear
            }),
            
            new this.classes.MiniCssExtractPlugin({
              filename: this.css,
            }),
        
           new webpack.IgnorePlugin({
              resourceRegExp: /legacy-js-api/
            })
          ];
        
          // Добавляем IsImages только если он не равен null
          if (this.IsImages) {
            basePlugins.push(this.IsImages);
          }
        
          return basePlugins;
    }

    resolve() { 
        return {
                extensions: [".tsx", ".ts", ".jsx", ".js", ".json"],
            alias: {
                "@models": this.path.resolve(this.dir, "src/models"),
                '@rocet': this.path.resolve(this.dir, 'vendor/pet/framework/Frontend/rocet/core'),
                '@libScss': this.path.resolve(this.dir, 'vendor/pet/framework/Frontend/css/tools'),
                '@src': this.path.resolve(this.dir, 'src'),
                '@tools': this.path.resolve(this.dir, 'vendor/pet/framework/Frontend/Tools'),
                '@lib': this.path.resolve(this.dir, 'vendor/pet/framework/Frontend/lib')

                
            }
        }
    }
     jsLoaders(){
        const loaders = [
          {
            loader: "babel-loader",
            options: this.babelOptions(),
          },
        ];
      
        if (this.isDev) loaders.push("eslint-loader");
      
        return loaders;
      };

    rules() { 
      return [
        { test: /\.css$/, use: this.cssLoaders() },
        { test: /\.less$/, use: this.cssLoaders("less-loader") },
        {
          test: /\.s[ac]ss$/, use: [...this.cssLoaders({
            loader: 'sass-loader',
            options: {
              sassOptions: {
                quietDeps: true,// Отключает предупреждения о депрекации
                implementation: require('sass'),
              }
            }
          })]
        },
        { test: /\.(png|jpg|svg|gif)$/, type: "asset/resource" },
        {
          test: /\.(ttf|woff|woff2|eot)$/, type: "asset/resource",
          generator: {
            filename: `${this.font}/[name][ext]`, // Путь к папке fonts
          },
        },
        { test: /\.xml$/, use: ["xml-loader"] },
        { test: /\.csv$/, use: ["csv-loader"] },
        { test: /\.js$/, exclude: /node_modules/, use: this.jsLoaders() },
        {
          test: /\.(ts|tsx|jsx)?$/,
          exclude: /node_modules/,
          loader: "ts-loader",
          options: { configFile: "tsconfig.json" }
        },
      ];
    }
}
const web = new Setting();
web.fs = require("fs");
web.path = require("path");
web.dir = __dirname + "/../../../../";
web.getENV();
web.classes = {
 
  HTMLWebpackPlugin: require("html-webpack-plugin"),
  CssMinimizerPlugin: require("css-minimizer-webpack-plugin"),
  TerserWebpackPlugin: require("terser-webpack-plugin"),
  MiniCssExtractPlugin: require("mini-css-extract-plugin"),
  CopyWebpackPlugin:require("copy-webpack-plugin"),
}

web.isDev = process.env.NODE_ENV === "development";
web.isProd = !web.isDev;
web.init();

const modulesW = {
  context: web.path.resolve(web.dir , "src"),
  mode: web.isDev ? "development" : "production",
  entry: web.pages.entry,
  output: {
    filename: "view/assets/js/[name]_[hash].js",
    path: web.path.resolve(web.dir, web.dist),
    assetModuleFilename: "view/assets/img/[name][ext][query]",
  },
  resolve: web.resolve(),
  optimization:  web.optimization(),
  plugins: web.plugins(),
  module: { rules:web.rules()}
};
module.exports = {
 webpack:modulesW
}