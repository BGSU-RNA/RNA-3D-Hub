
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
                    Please use the following citation when using this resource:
                      <blockquote>
                        <p>
                          Leontis, N. B., & Zirbel, C. L. (2012).
                          Nonredundant 3D Structure Datasets for RNA Knowledge Extraction and Benchmarking.
                          In N. Leontis & E. Westhof (Eds.), (Vol. 27, pp. 281–298). Springer Berlin Heidelberg. doi:10.1007/978-3-642-25740-7_13
                        </p>
                      </blockquote>
                    </p>
                    <p>
                    <span class="label notice">Notice</span>
                    PDB files with no full nucleotides are not included in the
                    non-redundant lists. For example, see PDB
                    <a href="http://www.rcsb.org/pdb/explore/explore.do?structureId=1DV4">1DV4</a>.
                    </p>
                    <p>
                    Unique and stable ids are assigned to all non-redundant
                    equivalence classes of structure files. Non-redundnant lists are
                    updated automatically every week, and
                    a versioning system is implemented to provide independent
                    access to data snapshots. Full description will appear in
                    a separate publication.
                    </p>
                    <p>
                    The old web interface is no longer developed or supported. It can be accessed
                    at <a href="http://rna.bgsu.edu/nrlist/oldsite.html">http://rna.bgsu.edu/nrlist/oldsite.html</a>.
                    </p>
                </div>
            </div>
          </div>
        </div>
      </div>


      <script>
          $(".pdb").click(LookUpPDBInfo);
      </script>