
function sortTable(n,table_id,data_type) {
  // adapted from https://www.w3schools.com/howto/howto_js_sort_table.asp
  // n is the column number
  // table_id tells which table
  // numeric is true if the column is numeric, false if alphabetic

  var table, rows, switching, i, j, x, y, check_next, shouldSwitch, dir, switchcount = 0;
  // Set the sorting direction to ascending at first
  dir = "asc";

  table = document.getElementById(table_id);
  rows = table.rows;

  if (data_type == 'checkbox') {
    var text = rows[8].getElementsByTagName("TD")[n].innerHTML;
    var id = text.split('id="')[1].split('"')[0]
  }

  // Make passes until no switches are made; bubble sort
  switching = true;
  while (switching) {
    switching = false;
    /* Loop through all table rows (except the
    first, which contains table headers): */
    for (i = 1; i < (rows.length - 1); i++) {

      // the current row entry
      if (data_type == 'alpha') {
        x = rows[i].getElementsByTagName("TD")[n].innerHTML.toLowerCase();
      } else if (data_type == 'numeric') {
        x = Number(rows[i].getElementsByTagName("TD")[n].innerHTML);
      } else if (data_type == 'checkbox') {
        var text = rows[i].getElementsByTagName("TD")[n].innerHTML;
        var id = text.split('id="')[1].split('"')[0]
        if (document.getElementById(id).checked) {
          x = 0;
        } else {
          x = 1;
        }
      } else {
        x = 0;
      }

      j = i;  // compare row i to row j
      check_next = true;

      // look for where row i should be pushed back to
      while (check_next & j < rows.length-1) {

        if (data_type == 'alpha') {
          y = rows[j+1].getElementsByTagName("TD")[n].innerHTML.toLowerCase();
        } else if (data_type == 'numeric') {
          y = Number(rows[j+1].getElementsByTagName("TD")[n].innerHTML);
        } else if (data_type == 'checkbox') {
          var text = rows[j+1].getElementsByTagName("TD")[n].innerHTML;
          var id = text.split('id="')[1].split('"')[0]
          if (document.getElementById(id).checked) {
            y = 0;
          } else {
            y = 1;
          }
        } else {
          y = 0;
        }

        if (dir == "asc") {
          if (x > y) {
            j++
          } else {
            check_next = false;
          }
        } else if (dir == "desc") {
          if (x < y) {
            j++
          } else {
            check_next = false;
          }
        }
      }

      // if row i belongs later in the table, put it there
      if (j > i) {
        // Move row i to just after row j
        rows[0].parentNode.insertBefore(rows[i], rows[j+1]);
        // Each time a switch is done, increase this count by 1:
        switchcount++;
        // still keep looking for switches to make
        switching = true;
      }
    }
    /* If no switching has been done AND the direction is "asc",
    set the direction to "desc" and run the while loop again. */
    if (switchcount == 0 && dir == "asc") {
      dir = "desc";
      switching = true;
    }
  }
}
