
window.onload = SetCursor();

//Sets the cursor on the first field
function SetCursor()
{
	var linBarcode = document.getElementById('linBarcode');

	if(linBarcode == null)
	{
		var vaccineList = document.getElementById('vaccineList');
		vaccineList.focus();
	}
	else
	{
		linBarcode.focus();
	}
}