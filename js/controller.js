var app = angular.module('App', ["ngRoute"]);

app.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
    $httpProvider.defaults.cache = false;
    if (!$httpProvider.defaults.headers.get) {
         $httpProvider.defaults.headers.get = {};
    }
    // disable IE ajax request caching
    $httpProvider.defaults.headers.get['If-Modified-Since'] = '0';
   
   $routeProvider
        .when("/instalaciones-entregas", {
            templateUrl : "pages/agenda.html",
            controller  : "lista_controller"
        })
        .otherwise({
            templateUrl : "pages/welcome.html"
        });
}]);

app.controller("lista_controller", function($scope, $http)
{
    $scope.lista = [];
    $scope.dia_seleccionado = 1;
    $scope.semanas = [];
    $scope.cargando_lista = false;
    $scope.semana_seleccionada;
    $scope.dia_de_semana = "";

    for(var i = 1; i<=52;  i++)
    {
        var desglose = {id: i, descripcion:''};
        $scope.semanas.push(desglose);
    }

    $scope.actualizar_lista = function(semana)
    {
        $scope.cargando_lista = true;
        var form = {};
        form['semana'] = semana;
        $http.post('rest/conexion.php', form).then(function(response)
        { 
            $scope.lista = response.data;
            $scope.cargando_lista = false;
        },function(response)
        {
            $scope.cargando_lista = false;
        });
    }
    $scope.actualizar_lista();

    $scope.actualizar_catalogos = function()
    {
        var form = {};
        form['accion'] = "catalogos";
        $http.post('rest/programacion.php', form).then(function(response)
        { 
            $scope.semanas = response.data.semanas;
            
        },function(response)
        {
        });
    }
    
    $scope.actualizar_catalogos();
    $scope.programar = function(obj, index)
    {
        
        var form = {};
        form['accion'] = "programar_fecha";
        form['empresa'] = obj.empresa;
        form['id'] = obj.id;
        $http.post('rest/programacion.php', form).then(function(response)
        { 
            $scope.lista[index].programado = 1;
            $scope.lista[index].fecha_programacion = $scope.lista[index].fecha_entrega;
            $scope.actualizar_lista($("#semanas_del_anio").val());
        },function(response)
        {
        });
    }

    $scope.cambiar_semana = function()
    {
        $scope.actualizar_lista($("#semanas_del_anio").val());
    }

    $scope.visualizar = function(id)
    {
        if($("#visualizar_"+id).is(":visible"))
            $("#visualizar_"+id).hide();
        else
            $("#visualizar_"+id).show();
        
    }

    $scope.programar_tarea = function(empresa, id)
    {
        var form = {};
        form['accion'] = "programar_fecha_tarea";
        form['empresa'] = empresa;
        form['id'] = id;
        form['fecha_programacion'] = $("#"+id).val();
        
        $http.post('rest/programacion.php', form).then(function(response)
        { 
            $scope.actualizar_lista($("#semanas_del_anio").val());
           
        },function(response)
        {
            //$scope.cargando = false;
        });
        
    }

    $scope.ver_info = function(data)
    {
        
        $scope.info = data;
        $scope.show_modal_info = true;
        $scope.cargando = true;
        $scope.detalle = [];

        var form = {};
        form['accion'] = "detalles";
        form['empresa'] = data.empresa;
        form['id'] = data.id_venta;
        $http.post('rest/programacion.php', form).then(function(response)
        { 
            console.log(response.data);
           $scope.detalle = response.data; 
           $scope.cargando = false;
        },function(response)
        {
            $scope.cargando = false;
        });
    }
});

app.filter('orderByValue', function () {
    // custom value function for sorting
    function myValueFunction(card) {
      return card.semana_actual + card.semana_actual;
    }

    function myValueFunction2(card) {
            return card.programado + card.programado;
    }

    function orderByDia(card) {
        return card.dia + card.dia;
    }
    return function (obj) {
        var array = [];
        var array2 = [];
        var array3 = [];
        console.log(obj);
        Object.keys(obj).forEach(function (key) {
          // inject key into each object so we can refer to it from the template
            obj[key].name = key;
            if(obj[key].semana_actual == 1)
            {
                if(obj[key].programado == 1)
                    array.push(obj[key]);
                if(obj[key].programado == 0)
                    array2.push(obj[key]);    
            }else{
                array3.push(obj[key]); 
            } 
        });

        array.sort(function (a, b) {
            return orderByDia(b) - orderByDia(a);
        });

        array2.sort(function (a, b) {
            return orderByDia(b) - orderByDia(a);
        });
          /*if(obj[key].programado == 0)
            array2.push(obj[key]);
        });*/
        // apply a custom sorting function
       

        /*array.sort(function (a, b) {
          return myValueFunction(b) - myValueFunction(a);
        });*/
        
        return array.concat(array2).concat(array3);
      };
});

/*app.filter('orderByDia', function () {
    // custom value function for sorting
    return function (obj) {
        var array = [];
        var array2 = [];
        var array3 = [];
        Object.keys(obj).forEach(function (key) {
          // inject key into each object so we can refer to it from the template
          obj[key].name = key;
          if(obj[key].dia == day)
          {
            if(obj[key].programado == 1)
            {
                if(obj[key].semana == 1)
                    array.push(obj[key]);
                else    
                    array2.push(obj[key]);
            }
          }
        });
        // apply a custom sorting function
       
        return array;
      };
});*/