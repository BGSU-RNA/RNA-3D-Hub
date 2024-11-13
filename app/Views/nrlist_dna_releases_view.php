
    <div class="container nr_all_releases_view">
      <div class="content">
        <div class="page-header">
          <h1><?=$title?>
            <br><small><?=$total_pdbs?> RNA-containing 3D structures</small>
          </h1>
        </div>
        <div class="row">
          <div class="span40p">
            <h2></h2>
            <?php echo $table;?>
          </div>
          <div class="span45p">
            <div class="row">
              <div class="span">
                <p>
                  The Representative Sets of DNA 3D Structures organize all
                  DNA-containing 3D structures from PDB into sequence/structure equivalence classes
                  and selects a high-quality representative structure from each class.
                  The resulting Representative Sets of DNA 3D structures are appropriate for tasks
                  which require searching or training over the breadth of the entire DNA 3D structure database,
                  but which should avoid the redundancy inherent in PDB due to multiple 3D
                  structures of the same molecule from the same organism.
                  Equivalence classes show all structures of the same molecule, and the associated heat maps
                  show all-against-all geometric comparisons of the structures within each class.
                </p>
                <p>
                  Representative sets of DNA 3D structures are in development.
                  We are starting with the same date as we used for representative sets of RNA 3D structures,
                  and we will fill in DNA releases as they would have been.
                  We will need to modify the methodology somewhat compared to RNA.
                <p>
                  Releases are generated weekly, and previous releases are available starting from 2011.
                  The default listing shows structures at 4 Angstrom resolution or better, but
                  different resolution thresholds are available for each release.
                  The set of representative structures can be viewed online along with information
                  about the resolution, experimental method, molecule name, species, and number of
                  equivalent structures.
                  Releases can also be downloaded and parsed by computer programs.
                  Some weeks, when many new structures are released, the representative set listing can
                  be delayed because of the time it takes to compute all-against-all geometric comparisons
                  within large equivalences classes such as Thermus thermophilus small ribosomal subunit.
                </p>
                <p>
                  Individual chains are named in the format XXXX|M|C, where XXXX is the PDB entry, M is the model number,
                  usually 1, and C is the chain identifier, one to four characters.
                  IFEs are made up of individual chains linked with + signs.
                </p>
                </p>
                <p>
                  Unique and stable ids are assigned to all equivalence classes
                  of structure files.
                  Representative sets are updated
                  automatically every week, and a versioning system is
                  implemented to provide independent access to data snapshots.
                </p>
                <p>
                  <span class="label notice">Notice</span>
                  PDB files with no full nucleotides are not included in the
                  representative sets. For example, see PDB
                  <a href="https://www.rcsb.org/structure/1DV4">1DV4</a>.
                </p>
              </div>
            </div>
            <br>
            <div class="row">
              <div class="span">
                <p>
                  Please use the following citation when using this resource:
                  <blockquote>
                    <p>
                      Leontis, N. B., &amp; Zirbel, C. L. (2012).

                      <a href="http://www.springerlink.com/content/u54511012r0344h3/">Nonredundant 3D Structure Datasets for RNA Knowledge Extraction and Benchmarking</a>.
                      In <a href="http://www.springerlink.com/content/978-3-642-25739-1">RNA 3D Structure Analysis and Prediction</a>
                      N. Leontis &amp; E. Westhof (Eds.), (Vol. 27, pp. 281–298). Springer Berlin Heidelberg. doi:10.1007/978-3-642-25740-7_13
                    </p>
                  </blockquote>
                </p>
                <p>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <script>
          $(".pdb").click(LookUpPDBInfo);
      </script>
