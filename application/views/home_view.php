  <body class="home">
    <div class="container">

      <!-- Main hero unit for a primary marketing message or call to action -->
      <div class="hero-unit">
        <h1>RNA 3D Hub</h1>
        <p>The single-stop destination for RNA structural annotations</p>
        <p>Public &szlig; release</p>
      </div>

      <div class="row">

        <div class="span5">
          <h2>RNA Loops</h2>
          <p>Hairpin, internal and three-way junction loops extracted from all RNA-containing PDB files. Automatic weekly updates.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>loops">View details &raquo;</a></p>
        </div>

        <div class="span6">
          <h2>RNA 3D Motif Atlas</h2>
          <p>Collection of recurrent RNA 3D motifs extracted from a non-redundant set of structures. Automatic updates coming soon!</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>motifs">View details &raquo;</a></p>
        </div>

        <div class="span5">
          <h2>Non-redundant Lists</h2>
          <p>PDB files grouped into equivalence classes by molecule type and biological organism. Automatic weekly updates.</p>
          <p><a class="btn primary" href="<?php echo $baseurl;?>nrlist">View details &raquo;</a></p>
       </div>

      </div>

      <footer>
        &copy; BGSU RNA group, 2012<br>
      </footer>

    </div> <!-- /container -->

    <!-- Google Analytics Tracking -->
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-9081629-8']);
      _gaq.push(['_trackPageview']);
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    </script>
    <!-- Google Analytics Tracking -->

  </body>
</html>