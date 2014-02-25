module.exports = function (grunt) {

    grunt.initConfig({

        phpunit: {
            classes: {
                dir: 'test/'
            }
        },

        watch: {
            scripts: {
                files: ['**/*'],
                tasks: ['phpunit'],
                options: { spawn: false }
            }
        }
    });

    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['phpunit']);
    grunt.registerTask('test', ['phpunit']);
};
