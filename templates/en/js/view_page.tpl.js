{literal}
function openHistory(pid)
{
    var features = 'width=520,height=400,top=30,left=30,resizable=no,scrollbars=yes,toolbar=no,location=no,menubar=no,status=no';
    var popupWin = window.open('{/literal}{$rel_url}{literal}history.php?pid=' + pid, '_impact', features);
    popupWin.focus();
}

function showDiv(p)
{
    if( document.getElementById(p).style.display == "block" )
    {
        document.getElementById(p).style.display = "none";
    }
    else
    {
        document.getElementById(p).style.display = "block";
    }
}
{/literal}