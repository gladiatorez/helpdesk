<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>        
      $(document).ready(function() {
      start();
      //let pingSpan = document.getElementById('pingSpan');
      let run;
      });
      
      function start() {
  run = true;
  pingTest();
}

function stop() {
  run = false;
  setTimeout(() => {
    pingSpan.innerHTML = "Stopped !";
  }, 500);
}


function pingTest() {

  if (run == true) { //Remove line
    let pinger = document.getElementById('pingTester');
    let start = new Date().getTime();
    pinger.setAttribute('src', 'https://helpdesk.kallagroup.co.id');
    pinger.onerror = () => {
      let end = new Date().getTime();
      // Change to whatever you want it to be, I've made it so it displays on the page directly, do whatever you want but keep the "end - start + 'ms'"
      pingtime = (end - start)/20;
      if (pingtime <= 100) {
         $("#bagus").show();
         $("#lemah").hide();
         $("#buruk").hide();
      } else if (pingtime > 100 && pingtime < 200) {
         $("#lemah").show();
          $("#bagus").hide();
          $("#buruk").hide();
      } else {
        $("#buruk").show();
        $("#bagus").hide();
        $("#lemah").hide();
      }
    }
    setTimeout(() => {
      pingTest(); 
    }, 1000);
  } // Remove this line too
}
      
      </script>
<v-footer app dark absolute class="app-footer" color="#1C1E21">
  <div class="container">
  <img id="pingTester" style="display: none;">
    <div class="row">
      

      <div class="col-16 col-md-16 text-right">
      
      <div style="display: flex; justify-content: space-around;"><div>&copy; 2023. CICT Kalla Group All Right Reserved</div>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<div id="bagus" style="text-align: right !important; display:none;"><el>Koneksi Bagus</el> &nbsp; <?php echo Asset::img('core::green.png', '', ['style' => 'float: right;width:24px;']); ?> </div>
<div id="lemah" style="text-align: right !important; display:none;"><el>Koneksi Lemah</el> &nbsp; <?php echo Asset::img('core::yellow.png', '', ['style' => 'float: right;width:24px;']); ?> </div>
<div id="buruk" style="text-align: right !important; display:none;"><el>Koneksi Buruk</el> &nbsp; <?php echo Asset::img('core::red.png', '', ['style' => 'float: right;width:24px;']); ?> </div>
      </div>

      </div>
    </div>
  </div>
</v-footer>