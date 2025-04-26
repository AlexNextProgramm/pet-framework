

export class Setting {
    path;
    fs
    pages = {
        html: [],
        entry: { root: "./root.tsx" }
    };
    IsImages = null;
    dir = '/'
    classes = { 
        MiniCssExtractPlugin,
        CssMinimizerPlugin,
        TerserWebpackPlugin,
        HTMLWebpackPlugin,
        CleanWebpackPlugin,
        CopyWebpackPlugin
    }
    isDev;
    isProd;
    constructor() {
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
                    minify: { collapseWhitespace: isProd }
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
    cssLoaders() { 
        const loaders = [
            MiniCssExtractPlugin.loader,
            "css-loader",
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
            
            new this.classes.CleanWebpackPlugin({
              cleanOnceBeforeBuildPatterns: [
                'view/CSS/**',
                'view/JS/**'
              ],
            }),
            
            new this.classes.MiniCssExtractPlugin({
              filename: "./view/CSS/[name][hash].css",
            }),
        
            // Uncomment for bundle analysis in production mode
            // ...(isProd ? [new BundleAnalyzerPlugin()] : []),
          ];
        
          // Добавляем IsImages только если он не равен null
          if (IsImages) {
            basePlugins.push(IsImages);
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
            options: babelOptions(),
          },
        ];
      
        if (this.isDev) loaders.push("eslint-loader");
      
        return loaders;
      };

    rules() { 
        return [
            { test: /\.css$/, use: this.cssLoaders() },
            { test: /\.less$/, use: this.cssLoaders("less-loader") },
            { test: /\.s[ac]ss$/, use: [...cssLoaders('sass-loader')] },
            { test: /\.(png|jpg|svg|gif)$/, type: "asset/resource" },
            { test: /\.(ttf|woff|woff2|eot)$/, use: ["file-loader"] },
            { test: /\.xml$/, use: ["xml-loader"] },
            { test: /\.csv$/, use: ["csv-loader"] },
            { test: /\.js$/, exclude:/node_modules/, use : this.jsLoaders() },
            { 
              test:/\.(ts|tsx|jsx)?$/,
              exclude:/node_modules/,
              loader:"ts-loader",
              options:{ configFile:"tsconfig.json" }
            },
          ],
    }
}