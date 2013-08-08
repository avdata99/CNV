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

    
function showData(nombre)
    {
    $("#dlgcont").show();
    $("#waiting").hide();    
    $("#nameaportar").html(nombre);
    $("#aportar").dialog({modal: true
                        , width: 450
                        ,resizable: false,
                        buttons:{enviar: enviarAporte,
                                  cerrar: function() { $( this ).dialog( "close" );}}
                        
                        });
    }
    
    enviarAporte = function(){
        $("#dlgcont").hide();
        $("#waiting").show();
        setTimeout(function()
            {
            $("#aportar").dialog("close");
            alert("GRACIAS POR COLABORAR CON NOSOTROS!");
            }, 3000);
            
        };
    