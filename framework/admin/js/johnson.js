(function() {
    tinymce.create('tinymce.plugins.johnson', {
        init : function(ed, url) {
            ed.addCommand('cmd_johnson', function() {
                ed.windowManager.open({
                    file : url + '/../johnson.php',
                    width : 610,
                    height : 510,
                    inline : 1,           
                }, {
                    content : ed.selection.getContent()
                });
            });
            ed.addButton('johnson', {
                title : 'Add/Edit Johnson Box', 
                cmd : 'cmd_johnson', 
                image: url + '/../images/jbox.png' 
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : "Johnson Boxes",
                author : 'M. Boomaars',
                authorurl : 'http://codingourweb.com',
                infourl : 'http://codingourweb.com',
                version : "0.1"
            };
        }
    });
    tinymce.PluginManager.add('johnson', tinymce.plugins.johnson);
})();