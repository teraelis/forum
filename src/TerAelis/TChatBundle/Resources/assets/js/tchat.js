(function() {
    var app = angular.module("TchatTerAelis", ['ngResource', 'ngSanitize']);

    app.controller("tchatController", ['$scope', '$sce', '$resource', function($scope, $sce, $resource) {
        console.log(Routing);
        var messagesRest = $resource(
          decodeURIComponent(Routing.generate(
            'teraelis_tchat_getLastMessage',
            {
              idSalon: ':salonId',
              timestamp: ':timestamp'
            }
          )),
          {salonId:'@salonId', timestamp:'@timestamp'}
        );
        var postMessage = $resource(
          decodeURIComponent(Routing.generate(
            'teraelis_tchat_show',
            {
              id: ':salonId'
            }
          )),
          {salonId:'@salonId'},
          {
            'post': {method: 'POST'}
          }
        );
        $scope.messages = [];
        $scope.lastUpdate = $('#lastUpdate').val();
        $scope.submitMessage = function() {
            var result = postMessage.post({
                salonId: $('#salonId').val(),
                message: $scope.message
            });
            result.$promise.then(function(response) {
              $scope.lastUpdate = Math.max(response.createdAt, $scope.lastUpdate);
            });
            $scope.message = "";
        };
        $scope.sce = $sce;

        $scope.refresh = function() {
            var messages = messagesRest.get(
                {salonId:$('#salonId').val(),'timestamp':$scope.lastUpdate},
                function() {
                    $scope.addMessages(messages.time, messages.messages);
                }
            );
        };

        $scope.addMessages = function(time, messages) {
            if(time > $scope.lastUpdate) {
                $scope.lastUpdate = time;
                if(messages.length > 0) {
                    for(var id in messages) {
                        messages[id].user = $sce.trustAsHtml(messages[id].user);
                        messages[id].message = $sce.trustAsHtml(messages[id].message);
                    }
                    $scope.messages = $scope.messages.concat(messages.sort(function(messageA, messageB) {
                        if(messageA.date < messageB.date)
                            return -1;
                        if(messageA.date > messageB.date)
                            return 1;
                        return 0;
                    }));
                }
            }
        };

        $scope.modMessage = function(mod, hide, id) {
            if(mod) {
                if(hide) {
                  var showMessageUrl = Routing.generate(
                    'teraelis_tchat_showMessage',
                    {
                      idMessage: id
                    }
                  );
                  return $sce.trustAsHtml('<a href="'+showMessageUrl+'">Réhabiliter le message</a>');
                } else {
                  var hideMessageUrl = Routing.generate(
                    'teraelis_tchat_hideMessage',
                    {
                      idMessage: id
                    }
                  );
                  return $sce.trustAsHtml("<a href='"+hideMessageUrl+"'>Cacher le message</a>");
                }
            } else {
                return $sce.trustAsHtml("");
            }
        };

        var watch = $scope.$watch(function() {
            return $scope.messages.length;
        }, function() {
            // Wait for templates to render
            $scope.$evalAsync(function() {
                // scroll down
                $("#messages")[0].scrollTop = $("#messages")[0].scrollHeight;
            });
        });

        setInterval(function() {
            $scope.refresh();
        }, 1000);
    }]);
})();

