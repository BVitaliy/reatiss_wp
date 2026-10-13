/**
 * Gulp build for the reatiss WordPress theme.
 *
 * Adapted from the static markup project: only the asset pipeline survives.
 * The build/zip/deploy tasks are gone — a WP theme ships via git/rsync, not a
 * zip of build/.
 *
 * Tasks:
 *   gulp            — styles + img, BrowserSync proxy to the local WP site, watch
 *   gulp styles     — scss/ → css/
 *   gulp img        — images/ → img/ (webp siblings + optimized originals)
 *   gulp updateJS   — refresh js/vendors/ from node_modules
 *   gulp updateCSS  — refresh css/vendors/ (Swiper, lightGallery)
 *   gulp updateLgAssets — lightGallery icon font + loader → css/fonts, css/images
 */

import gulp from "gulp";
import { deleteAsync } from "del";
import browsersync from "browser-sync";
import notify from "gulp-notify";
import plumber from "gulp-plumber";
import * as dartSass from "sass";
import gulpSass from "gulp-sass";
import cleanCss from "gulp-clean-css";
import autoprefixer from "gulp-autoprefixer";
import newer from "gulp-newer";
import imagemin from "gulp-imagemin";
import webp from "gulp-webp";
import { configDotenv } from "dotenv";

const scss = gulpSass(dartSass);

configDotenv();

// Local WP site URL. Override in .env if MAMP runs on another port/host.
const WP_URL = process.env.WP_DEV_URL || "http://localhost:8888/reatiss_wp";

export const styles = () => {
  return gulp
    .src(["scss/**/*.scss"])
    .pipe(
      plumber(
        notify.onError({
          title: "SCSS Error",
          message: "Error: <%= error.message %>",
        })
      )
    )
    .pipe(scss({ outputStyle: "expanded" }))
    .pipe(
      autoprefixer({
        cascade: false,
        grid: true,
        flexbox: true,
        overrideBrowserslist: ["last 5 versions", "> 1%", "not dead"],
      })
    )
    .pipe(
      cleanCss({
        format: {
          breaks: {
            afterAtRule: 0,
            afterBlockBegins: 1,
            afterBlockEnds: 2,
            afterComment: 1,
            afterProperty: 0,
            afterRuleBegins: 0,
            afterRuleEnds: 1,
            beforeBlockEnds: 1,
            betweenSelectors: 1,
          },
          spaces: {
            aroundSelectorRelation: false,
            beforeBlockBegins: true,
            beforeValue: true,
          },
          semicolonAfterLastProperty: true,
        },
        level: 0,
      })
    )
    .pipe(gulp.dest("css/"))
    .pipe(browsersync.stream());
};

// Source images live in images/ (gitignored). Optimized output goes to img/,
// which IS committed — img/ is the only stored copy of theme-side assets.
export const img = () => {
  return gulp
    .src("images/**/*.{png,jpg,jpeg}", { allowEmpty: true })
    .pipe(newer({ dest: "img/", ext: ".webp" }))
    .pipe(webp({ quality: 75 }))
    .pipe(gulp.dest("img/"))
    .pipe(gulp.src("images/**/*.{png,jpg,jpeg,svg}", { allowEmpty: true }))
    .pipe(newer("img/"))
    .pipe(
      imagemin({
        optimizationLevel: 5,
        interlaced: true,
        progressive: true,
        svgoPlugins: [{ removeViewBox: false }],
      })
    )
    .pipe(gulp.dest("img/"));
};

export const cleanImg = () => deleteAsync("images/**/*", { force: true });

export const updateJS = () => {
  return gulp
    .src([
      "node_modules/jquery/dist/jquery.min.js",
      "node_modules/swiper/swiper-bundle.min.js",
      "node_modules/inputmask/dist/jquery.inputmask.min.js",
      "node_modules/gsap/dist/gsap.min.js",
      "node_modules/gsap/dist/ScrollTrigger.min.js",
      "node_modules/gsap/dist/SplitText.min.js",
      "node_modules/gsap/dist/MotionPathPlugin.min.js",
      "node_modules/gsap/dist/DrawSVGPlugin.min.js",
      "node_modules/lenis/dist/lenis.min.js",
      "node_modules/lightgallery/lightgallery.min.js",
      "node_modules/lightgallery/plugins/thumbnail/lg-thumbnail.min.js",
      "node_modules/lightgallery/plugins/zoom/lg-zoom.min.js",
      "node_modules/@google/model-viewer/dist/model-viewer.min.js", // ES module, loaded on demand (app-model.js)
    ])
    .pipe(gulp.dest("js/vendors/"));
};

export const updateCSS = () => {
  return gulp
    .src(
      [
        "node_modules/swiper/swiper-bundle.min.css",
        "node_modules/lightgallery/css/lightgallery-bundle.min.css",
      ],
      { allowEmpty: true }
    )
    .pipe(gulp.dest("css/vendors/"));
};

// lightGallery's icon font + loader (the bundle css points at ../fonts, ../images)
export const updateLgAssets = () => {
  return gulp
    .src(["node_modules/lightgallery/{fonts,images}/*"], { encoding: false })
    .pipe(gulp.dest("css/"));
};

export const server = () => {
  browsersync.init({
    ui: false,
    port: 3000,
    notify: false,
    open: false,
    cors: true,
    proxy: WP_URL,
  });
};

export const refresh = (done) => {
  browsersync.reload();
  done();
};

function startWatch() {
  gulp.watch("scss/**/*.scss", styles);
  gulp.watch("js/**/*.js").on("change", refresh);
  gulp.watch(["**/*.php", "!node_modules/**"]).on("change", refresh);
  gulp.watch("images/**/*", img);
}

gulp.task("default", gulp.parallel(styles, img, server, startWatch));
