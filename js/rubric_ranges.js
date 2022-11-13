M.gradingform_rubric_ranges = { 'name' : null, 'Y' : null};

/**
 * This function is called for each rubric on page.
 */
M.gradingform_rubric_ranges.init = function(Y, options) {
    M.gradingform_rubric_ranges.name = options.name
    M.gradingform_rubric_ranges.Y = Y
    Y.on('click', M.gradingform_rubric_ranges.levelclick, '#rubric-'+options.name+' .level', null, Y, options.name);
    // Capture also space and enter keypress.
    Y.on('key', M.gradingform_rubric_ranges.levelclick, '#rubric-' + options.name + ' .level', 'space', Y, options.name);
    Y.on('key', M.gradingform_rubric_ranges.levelclick, '#rubric-' + options.name + ' .level', 'enter', Y, options.name);

    Y.all('#rubric-'+options.name+' .radio').setStyle('display', 'none')
    Y.all('#rubric-'+options.name+' .level').each(function (node) {
      if (node.one('input[type=radio]').get('checked')) {
        node.addClass('checked');
      }
    });

    Y.all('#rubric-'+options.name+' .points').each( function(node) {
      if (node.one('input[type=text]')) {
        node.one('input[type=text]').on('keypress', M.gradingform_rubric_ranges.onlynumbers)
        node.one('input[type=text]').on('keyup', M.gradingform_rubric_ranges.selectrange)
      }
  });
};

M.gradingform_rubric_ranges.selectrange = function(e) {
  var el = e.target
  var Y = M.gradingform_rubric_ranges.Y

  var gradepoints = parseInt(el.get('value'));
  if (!isNaN(gradepoints)) {
    var range = [];
    el.get('parentNode').get('previousSibling').all('.scorevalue').each(function (node) {
      range = node.get('innerHTML').split(' to ');
      if (gradepoints >= range[0] && gradepoints <= range[1]) {
        if (!node.ancestor('td .level').hasClass('checked')) {
          Y.Event.simulate(node.ancestor('td .level').getDOMNode(), 'click')
        }
      }
    })
  }
}

M.gradingform_rubric_ranges.getmaxpoints = function(el) {
  // Get max value point.
  var maxpointsparts = el.get("nextSibling").get('innerHTML').split(' ');
  var maxpoints = 0;
  maxpointsparts.forEach(function (item, index) {
    if (!isNaN(item)) {
      maxpoints = parseInt(item);
    }
  });
  return maxpoints;
}
M.gradingform_rubric_ranges.onlynumbers = function(e) {
  var el = e.target
  // Handle paste
  if (e.type === 'paste') {
      key = e.clipboardData.getData('text/plain');
  } else {
  // Handle key press
      var key = e.keyCode || e.which;
      key = String.fromCharCode(key);
  }
  var regex = /[0-9]/;
  if( !regex.test(key) ) {
      e.returnValue = false;
      if(e.preventDefault) e.preventDefault();
  } else {
    var maxpoints = M.gradingform_rubric_ranges.getmaxpoints(el);
    var gradeval = el.get('value') + key;
    if(parseInt(gradeval) > maxpoints) {
      e.returnValue = false;
      if(e.preventDefault) e.preventDefault();
    }
  }
}
M.gradingform_rubric_ranges.levelclick = function(e) {
    var el = e.target
    while (el && !el.hasClass('level')) el = el.get('parentNode')
    if (!el) return
    e.preventDefault();
    el.siblings().removeClass('checked');

    // Set aria-checked attribute for siblings to false.
    el.siblings().setAttribute('aria-checked', 'false');
    chb = el.one('input[type=radio]')
    if (!chb.get('checked')) {
        chb.set('checked', true)
        el.addClass('checked')
        // Set aria-checked attribute to true if checked.
        el.setAttribute('aria-checked', 'true');
    } else {
        el.removeClass('checked');
        // Set aria-checked attribute to false if unchecked.
        el.setAttribute('aria-checked', 'false');
        el.get('parentNode').all('input[type=radio]').set('checked', false)
    }
}
