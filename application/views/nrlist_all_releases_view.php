
    <div class="container nr_all_releases_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?=$title?>
            <small><?=$total_pdbs?> RNA-containing 3D structures</small>
          </h1>
        </div>
        <div class="row">
          <div class="span8">

            <h2></h2>
            <?php echo $table;?>

          </div>
          <div class="span7">
            <div class="row">
                <div class="span7">
                    <?=$images?>
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="span7">
                    <p>
                    RNA 3D Hub hosts non-redundant sets of RNA-containing 3D structures
                    obtained according to the methodology described in the book chapter
                    <a href="http://www.springerlink.com/content/u54511012r0344h3/">
                    Nonredundant 3D Structure Datasets for RNA Knowledge Extraction and Benchmarking</a>
                    that appeared in the book
                    <a href="http://www.springerlink.com/content/978-3-642-25739-1">RNA 3D Structure Analysis and Prediction</a>
                    edited by Professors Leontis and Westhof.
                    </p>
                    <p>
                    PDB files with no full nucleotides are not included in the
                    non-redundant lists. For example, see PDB
                    <a href="http://www.rcsb.org/pdb/explore/explore.do?structureId=1DV4">1DV4</a>.
                    </p>
                    <p>
                    Unique and stable ids are assigned to all non-redundant
                    equivalence classes of structure files. Non-redundnant lists are
                    updated automatically on a regular schedule, and
                    a versioning system is implemented to provide independent
                    access to data snapshots. Full description will appear in
                    a separate publication.
                    </p>
                    <p>
                    The older web interface for the non-redundant lists is still available
                    at <a href="http://rna.bgsu.edu/nrlist">http://rna.bgsu.edu/nrlist</a>, but
                    it will be discontinued some time in 2013.
                    </p>
                </div>
            </div>
          </div>
        </div>
      </div>


      <script>
          $(".pdb").click(LookUpPDBInfo);
      </script>