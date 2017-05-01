
function isAvailable() {
     window.plugins.touchid.isAvailable(
                function() {alert('available!')}, // success handler: TouchID available
                function(msg) {alert('not available, message: ' + msg)} // error handler: no TouchID available
            );
  }
document.getElementById('touchIDButton').addEventListener('click', isAvailable);