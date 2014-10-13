module.exports = function(grunt) { 

    var requirejs   = grunt.config('requirejs') || {};
    var clean       = grunt.config('clean') || {};
    var copy        = grunt.config('copy') || {};

    var root        = grunt.option('root');
    var libs        = grunt.option('mainlibs');
    var ext         = require(root + '/tao/views/build/tasks/helpers/extensions')(grunt, root);

    /**
     * Remove bundled and bundling files
     */
    clean.filemanagerbundle = ['output',  root + '/filemanager/views/js/controllers.min.js'];
    
    /**
     * Compile tao files into a bundle 
     */
    requirejs.filemanagerbundle = {
        options: {
            baseUrl : '../js',
            dir : 'output',
            mainConfigFile : './config/requirejs.build.js',
            paths : { 'filemanager' : root + '/filemanager/views/js' },
            modules : [{
                name: 'filemanager/controller/routes',
                include : ext.getExtensionsControllers(['filemanager']),
                exclude : ['mathJax', 'mediaElement'].concat(libs)
            }]
        }
    };

    /**
     * copy the bundles to the right place
     */
    copy.filemanagerbundle = {
        files: [
            { src: ['output/filemanager/controller/routes.js'],  dest: root + '/filemanager/views/js/controllers.min.js' },
            { src: ['output/filemanager/controller/routes.js.map'],  dest: root + '/filemanager/views/js/controllers.min.js.map' }
        ]
    };

    grunt.config('clean', clean);
    grunt.config('requirejs', requirejs);
    grunt.config('copy', copy);

    // bundle task
    grunt.registerTask('filemanagerbundle', ['clean:filemanagerbundle', 'requirejs:filemanagerbundle', 'copy:filemanagerbundle']);
};
