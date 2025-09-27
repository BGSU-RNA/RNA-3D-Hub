  // Function to handle shift-click checkbox selection
  function handleShiftClick(event) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"].jmolInline');
    let startIdx = -1;
    let endIdx = -1;

    // Find the indexes of the clicked checkbox and the previously clicked checkbox
    checkboxes.forEach((checkbox, index) => {
      if (checkbox === event.target || checkbox === previousCheckbox) {
        if (startIdx === -1) {
          startIdx = index;

        } else {
          endIdx = index;
        }
      }
    });

    // Update the checkboxes in the selected range
    if (startIdx !== -1 && endIdx !== -1) {
      const startIndex = Math.min(startIdx, endIdx);
      const endIndex = Math.max(startIdx, endIdx);
      for (let i = startIndex+1; i < endIndex; i++) {
        checkboxes[i].checked = true;
        $('#' + checkboxes[i].id).jmolShow();
      }
    }
  }

  // Add event listeners to checkboxes for shift-click functionality
  const checkboxes = document.querySelectorAll('input[type="checkbox"].jmolInline');

  //console.log(checkboxes)

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener('click', (event) => {
      if (event.shiftKey) {
        handleShiftClick(event);
      } else {
        // Store the clicked checkbox if not shift-clicking
        if (event.srcElement.className=='jmolInline') {
          previousCheckbox = event.target;
        }
      }
    });
  });
