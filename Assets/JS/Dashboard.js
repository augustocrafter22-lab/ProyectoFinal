
    new Chart(document.getElementById("donut").getContext("2d"), {
      type: 'doughnut',
      data: {
        labels: ['Abiertas', 'En proceso', 'Resueltas', 'Cerradas'],
        datasets: [{
          data: [8, 5, 23, 7],
          backgroundColor: ['#ef4444', '#f97316', '#22c55e', '#94a3b8'],
          borderWidth: 2,
          borderColor: '#fff',
          hoverOffset: 4
        }]
      },
      options: {
        cutout: '62%',
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: c => ` ${c.label}: ${c.raw}`
            }
          }
        },
        animation: {
          animateRotate: true,
          duration: 700
        }
      }
    });
  
