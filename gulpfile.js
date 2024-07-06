const gulp = require('gulp');

//  HTML
//  Подключение частей html кода
const fileInclude = require('gulp-file-include');
// Сжатие итогого html
const htmlClean = require('gulp-htmlclean');

//  Препроцессор SCSS
const sass = require('gulp-sass')(require('sass'));
const sassGlob = require('gulp-sass-glob');
//  Автопрефиксер
const autoprefixer = require('gulp-autoprefixer');
//  Сжатие итогого main.css файла
const csso = require('gulp-csso');

// JS
const webpack = require('webpack-stream');
const babel = require('gulp-babel')

//  Сервер для автообновления страницы
const server = require('gulp-server-livereload');
//  Удаление папки
const clean = require('gulp-clean');
// Для работы с файловой системой 
// (нужно в случаях к примеру когда мы пытаемся удалить не существующую папку и используя работу с файловой системой мы можем через код проверить а существует ли такая папка и в зависимости от ответа использовать какую то логику)
const fs = require('fs');
// Для того чтобы было понятно к какому scss файлу относятся стили при просмотре через DevTools 
const sourceMaps = require('gulp-sourcemaps');
// Уведомления об ошибках
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');




//  ЗАДАЧИ
//  ===> HTML
gulp.task('includeFiles', function () {
    return gulp
        // Указываем откуда берем файлы
        .src(['./src/html/**/*.html', '!./src/html/blocks/*.html'])
        .pipe(plumber({
            errorHandler: notify.onError({
                title: "HTML",
                message: "Error <%= error.message %>",
                sound: false
            })
        }))
        // Подключение частей html кода
        .pipe(fileInclude({
            prefix: '@@',
            basepath: '@file'
        }))
        // .pipe(htmlClean())
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/'))
});

//  ===> SCSS
gulp.task('sass', function () {
    return gulp
        // Указываем откуда берем файлы
        .src('./src/scss/*.scss')
        .pipe(plumber({
            errorHandler: notify.onError({
                title: "Styles",
                message: "Error <%= error.message %>",
                sound: false
            })
        }))
        .pipe(sourceMaps.init())
        .pipe(autoprefixer())
        .pipe(sassGlob())
        .pipe(sass())
        // .pipe(csso())
        .pipe(sourceMaps.write())
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/css/'))
});

//  ===> JS
gulp.task('js', function () {
    return gulp.src('./src/js/*.js')
        // Указываем откуда берем файлы
        .pipe(plumber({
            errorHandler: notify.onError({
                title: "JS",
                message: "Error <%= error.message %>",
                sound: false
            })
        }))
        .pipe(babel())
        .pipe(webpack(require("./webpack.config.js")))
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/js/'))
});

//  ===> Копирование картинок в итоговую папку
gulp.task('copyImages', function () {
    return gulp
        // Указываем откуда берем файлы
        .src('./src/img/**/*')
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/img/'))
});

//  ===> Копирование шрифтов в итоговую папку
gulp.task('copyFonts', function () {
    return gulp
        // Указываем откуда берем файлы
        .src('./src/fonts/**/*')
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/fonts/'))
});

//  ===> Копирование различных файлов в итоговую папку
gulp.task('copyFiles', function () {
    return gulp
        // Указываем откуда берем файлы
        .src('./src/files/**/*')
        // Указываем куда сохраняем файлы
        .pipe(gulp.dest('./dist/files/'))
});

//  ===> Сервер для автообновления страницы
gulp.task('server', function () {
    return gulp
        // Указываем откуда берем файлы
        .src('./dist/')
        .pipe(server({
            livereload: true,
            open: true
        }))
});

//  ===> Удаление папки
gulp.task('clean', function (done) {
    if (fs.existsSync('./dist/')) {
        return gulp
            .src('./dist/')
            .pipe(clean())
            .pipe(clean({ force: true }));
    }
    done();
});

//  ===> WATCHER
gulp.task('watch', function () {
    gulp.watch('./src/html/**/*.html', gulp.parallel('includeFiles'));
    gulp.watch('./src/scss/**/*.scss', gulp.parallel('sass'));
    gulp.watch('./src/js/**/*.js', gulp.parallel('js'));
    gulp.watch('./src/img/**/*', gulp.parallel('copyImages'));
    gulp.watch('./src/fonts/**/*', gulp.parallel('copyFonts'));
    gulp.watch('./src/files/**/*', gulp.parallel('copyFiles'));
});


// ЗАПУСК СБОРКИ
// (Пишем в терминале команду "npm i", а после команду"gulp")
gulp.task('default', gulp.series(
    'clean',
    gulp.parallel('includeFiles',
        'sass',
        'copyImages',
        'copyFonts',
        'copyFiles',
        'js'),
    gulp.parallel('server',
        'watch')
));


