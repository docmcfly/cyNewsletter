function search() {
  let pattern = $('#fastSearch').val().toUpperCase().replaceAll(" ",String.fromCharCode(160))
  
  let f = false
  $(".fullName").each(function() {
  	let name = $(this).text().toUpperCase()
  	if( !f && name.search(pattern) >= 0 ) {
  		$(this).get(0).scrollIntoView({ behavior: 'smooth' });
  		f = true
  	}
  })
}

$("#fastSearchButton").click( search ); 
$("#fastSearch").keypress( function(event) {
     if (event.key === "Enter") {
 		search();
 		event.preventDefault();
     }
   })