const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const webpack  = require("webpack");

class Setting {
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

    getPage() {
        const dir = this.path.resolve( this.dir, "dist/view/page")
        const page = this.fs.readdirSync(dir);
        page.forEach((fileAndDir) => {
            const file = dir + '/' + fileAndDir;
            if (this.fs.statSync(file).isDirectory()) {
                const name = fileAndDir
                this.pages.entry[name] = `./page/${name}.ts`;
                this.pages.html.push({
                    filename: './view/page/' + `${name}/head.php`,
                    template: './head.php',
                    entry: this.path.join(this.dir, "src/page", `${name}.ts`),
                    chunks: ['root', name],
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
                    to: "view/images",
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
              cleanOnceBeforeBuildPatterns: [
                'view/CSS/**',
                'view/JS/**'
              ],
            }),
            
            new this.classes.MiniCssExtractPlugin({
              filename: "./view/CSS/[name][hash].css",
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
                '@rocet': this.path.resolve(this.dir, 'vendor/pet/framework/Frontend/rocet/core')
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
          })]},
        { test: /\.(png|jpg|svg|gif)$/, type: "asset/resource" },
        { test: /\.(ttf|woff|woff2|eot)$/, use: ["file-loader"] },
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
web.dir = __dirname+"/../../../../"
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
    filename: "view/JS/[name]_[hash].js",
    path: web.path.resolve(web.dir, "dist"),
    assetModuleFilename: "view/images/[name][ext][query]",
  },
  resolve: web.resolve(),
  optimization:  web.optimization(),
  plugins: web.plugins(),
  module: { rules:web.rules()}
};
module.exports = {
 webpack:modulesW
}