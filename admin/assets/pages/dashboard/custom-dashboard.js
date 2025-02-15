'use strict';
jQuery(document).ready(function () {
  floatchart();
  jQuery(window).on('resize', function () {
    floatchart();
  });
  $('#mobile-collapse').on('click', function () {
    setTimeout(function () {
      floatchart();
    }, 700);
  });
  $(function () {
    $('#selectall').click(function () {
      $('.case').attr('checked', this.checked);
    });
    $('.case').click(function () {
      if ($('.case').length == $('.case:checked').length) {
        $('#selectall').attr('checked', 'checked');
      } else {
        $('#selectall').removeAttr('checked');
      }
    });
  });
});

function floatchart() {
  $(function () {
    //flot options
    var options = {
      legend: {
        show: false,
      },
      series: {
        label: '',
        curvedLines: {
          active: true,
          nrSplinePoints: 20,
        },
      },
      tooltip: {
        show: true,
        content: 'x : %x | y : %y',
      },
      grid: {
        hoverable: true,
        borderWidth: 0,
        labelMargin: 0,
        axisMargin: 0,
        minBorderMargin: 0,
      },
      yaxis: {
        min: 0,
        max: 30,
        color: 'transparent',
        font: {
          size: 0,
        },
      },
      xaxis: {
        color: 'transparent',
        font: {
          size: 0,
        },
      },
    };

    //plotting
    $.plot(
      $('#total-value-graph-1'),
      [
        {
          data: [
            [0, 20],
            [20, 10],
            [35, 18],
            [50, 12],
            [65, 25],
            [75, 10],
            [90, 20],
          ],
          color: '#fff',
          lines: {
            show: true,
            fill: true,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );
    $.plot(
      $('#total-value-graph-2'),
      [
        {
          data: [
            [0, 10],
            [20, 20],
            [35, 18],
            [50, 25],
            [65, 12],
            [75, 10],
            [90, 20],
          ],
          color: '#fff',
          lines: {
            show: true,
            fill: true,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );
    $.plot(
      $('#total-value-graph-3'),
      [
        {
          data: [
            [0, 20],
            [20, 10],
            [35, 25],
            [50, 18],
            [65, 18],
            [75, 10],
            [90, 12],
          ],
          color: '#fff',
          lines: {
            show: true,
            fill: true,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );
    $.plot(
      $('#total-value-graph-4'),
      [
        {
          data: [
            [0, 18],
            [20, 10],
            [35, 20],
            [50, 10],
            [65, 12],
            [75, 25],
            [90, 20],
          ],
          color: '#fff',
          lines: {
            show: true,
            fill: true,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );

    $.plot(
      $('#power-card-chart1'),
      [
        {
          data: [
            [0, 18],
            [20, 10],
            [35, 20],
            [50, 10],
            [65, 27],
            [75, 15],
            [90, 20],
          ],
          color: '#ff5252',
          lines: {
            show: true,
            fill: false,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );
    $.plot(
      $('#power-card-chart2'),
      [
        {
          data: [
            [0, 10],
            [20, 25],
            [35, 27],
            [50, 10],
            [65, 20],
            [75, 10],
            [90, 18],
          ],
          color: '#448aff',
          lines: {
            show: true,
            fill: false,
            lineWidth: 3,
          },
          points: {
            show: false,
          },
          //curve the line  (old pre 1.0.0 plotting function)
          curvedLines: {
            apply: true,
          },
        },
      ],
      options
    );
  });
}
