function lineToStr(node)
    {
    var source = node.source;
    var target = node.target;
    
    showData(source.name + " ("+getTypeNode(source)+") o "+target.name+" ("+getTypeNode(target)+")");
    
    }
    
function nodeToStr(node)
    {
    showData(node.name + " ("+getTypeNode(node)+")");
    }
    
    
/**
 * Obtener el tipo de dato 
 * @returns string "Represor" | "Grupo represor" | "Empresario" | "Empresa"
 */
function getTypeNode(nd)
    {
    if (nd.targetLinks.length === 0) return "Grupo represor";
    if (nd.sourceLinks.length === 0) return "Empresa";
    if (nd.sourceLinks[0].source.targetLinks[0].source.targetLinks.length === 0) return "Represor";
    return "Empresario";
    }

    
function showData(nombre) {
    $("#dlgcont").show();
    $("#waiting").hide();    
    $("#nameaportar, input[name='name']").html(nombre);
    Recaptcha.reload();

    $("#aportar").dialog({
      modal: true,
      width: 450,
      resizable: false,
      buttons: {
        enviar: function() { $("form#contact").submit(); },
        cerrar: function() { $( this ).dialog( "close" );}
      }
    });
};

function handleContactSubmit() {
  $("form#contact").submit(function(event) {
    event.preventDefault();

    $("#dlgcont").hide();
    $("#waiting").show();

    var msg = null;
    $.post("contact.php", $(this).serialize(), function(res) {
      alert("¡Gracias por colaborar con nosotros!");
    }).fail(function(res) {
      alert("Error: " + res.responseText);
      console.log(JSON.stringify(res));
    }).always(function() {
      $("#aportar").dialog("close");
    });
  });
}
