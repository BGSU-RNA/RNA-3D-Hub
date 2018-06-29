
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
                <p>
                  With Release 3.0, we modified the procedure for choosing the representative
                  of each equivalence class.  The representative is now chosen as the IFE (Integrated Functional Element)
                  which optimizes a combination of resolution, RSR, RSCC, Rfree, percent clash,
                  and the fraction of the molecule observed.  The intention is to select the structure
                  with the best experimental evidence for the coordinates being reported.
                </p>
              </div>
            </div>
            <br><br>
            <div class="row">
              <div class="span7">
                <p>
                  With Release 2.0, we <a href="http://rna.bgsu.edu/main/upgrading-rna-3d-hub/">upgraded the BGSU RNA Site</a>
                  to include new RNA 3D structures distributed in
                  <a href="http://wwpdb.org/news/news.php?year=2014#10-December-2014">mmCIF format</a>.
                  From each RNA-containing 3D structure file, we identify
                  Integrated Functional Elements (IFEs, for example, the
                  ribosomal small subunit, the eukaryotic large subunit
                  including the 5.8S rRNA, small duplexes, or other basepaired
                  structures consisting of multiple chains), group them by
                  sequence, structure, and species into equivalence classes,
                  and from each equivalence class, we select a representative.
                  The collection of representatives is referred to as
                  a representative set (formerly non-redundant (NR) list), but
                  note that each representative set will contain one instance
                  of homologous IFEs from different species. In September of
                  2016, we calculated release 2.0 and continued with subsequent
                  weekly releases.
                </p>
              </div>
            </div>
            <br><br>
            <div class="row">
              <div class="span7">
                <p>
                  The BGSU RNA Site hosts representative sets (a.k.a.
                  non-redundant lists) of RNA-containing 3D structures obtained
                  according to the methodology described in the book chapter
                  <a href="http://www.springerlink.com/content/u54511012r0344h3/">
                  Nonredundant 3D Structure Datasets for RNA Knowledge
                  Extraction and Benchmarking</a> that appeared in the book
                  <a href="http://www.springerlink.com/content/978-3-642-25739-1">RNA 3D Structure Analysis and Prediction</a>
                  edited by Professors Leontis and Westhof.
                </p>
                <p>
                  Please use the following citation when using this resource:
                  <blockquote>
                    <p>
                      Leontis, N. B., &amp; Zirbel, C. L. (2012).
                      Nonredundant 3D Structure Datasets for RNA Knowledge Extraction and Benchmarking.
                      In N. Leontis &amp; E. Westhof (Eds.), (Vol. 27, pp. 281–298). Springer Berlin Heidelberg. doi:10.1007/978-3-642-25740-7_13
                    </p>
                  </blockquote>
                </p>
                <p>
                  <span class="label notice">Notice</span>
                  PDB files with no full nucleotides are not included in the
                  representative sets. For example, see PDB
                  <a href="http://www.rcsb.org/pdb/explore/explore.do?structureId=1DV4">1DV4</a>.
                </p>
                <p>
                  Unique and stable ids are assigned to all equivalence classes
                  of structure files. Representative sets are updated
                  automatically every week, and a versioning system is
                  implemented to provide independent access to data snapshots.
                  Full description will appear in a separate publication.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
          $(".pdb").click(LookUpPDBInfo);
      </script>
