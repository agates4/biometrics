cordova.define('cordova/plugin_list', function(require, exports, module) {
module.exports = [
    {
        "file": "plugins/cordova-plugin-touch-id/www/TouchID.js",
        "id": "cordova-plugin-touch-id.TouchID",
        "pluginId": "cordova-plugin-touch-id",
        "clobbers": [
            "window.plugins.touchid"
        ]
    }
];
module.exports.metadata = 
// TOP OF METADATA
{
    "cordova-plugin-touch-id": "3.2.0",
    "cordova-plugin-whitelist": "1.3.2"
}
// BOTTOM OF METADATA
});