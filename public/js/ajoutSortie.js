$('#ville').change(function (){
    var villeId = $(this).val();//Récupèrer l'ID de la ville sélectionnée

    //Effectuer la requête AJAX
    $.ajax({
        url: '/sortie/lieux-par-ville/' + villeId,//Ici on met l'URL vers l'action du Controller
        type: 'GET',
        success: function (data) {
            //Mettre à jour la liste des lieux dans le formulaire
            $('#lieu').html(data);

        },
        error:function(xhr, status, error){
            console.error(error);
        }
    })
})