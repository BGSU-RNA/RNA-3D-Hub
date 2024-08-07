
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
                <p>
                <b>
                Releases 3.339 and 3.340 had a problem with ranking of IFEs.
                They were rebuilt on June 23, 2024.
                With release 3.339, we fixed problems with some files that could not be processed, such as
                3ZP8, and produced IFEs between "structured" and "unstructured" chains
                including, for example, 3ZP8|1|A and 3ZP8|1|B, for the first time.  That resulted in a number of
                new or changed equivalence classes.
              </b>
                  <p>
                  The Representative Sets of RNA 3D Structures organize all
                  RNA-containing 3D structures from PDB into sequence/structure equivalence classes
                  and selects a high-quality representative structure from each class.
                  The resulting Representative Sets of RNA 3D structures are appropriate for tasks
                  which require searching or training over the breadth of the entire RNA 3D structure database,
                  but which should avoid the redundancy inherent in PDB due to multiple 3D
                  structures of the same molecule from the same organism.
                  Equivalence classes show all structures of the same molecule, and the associated heat maps
                  show all-against-all geometric comparisons of the structures within each class.
                </p>
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
                  With release 3.333, we improved the treatment of taxonomic ids, mapping the taxid
                  associated with the PDB chain to a single taxid for the species, so that different
                  strains of the same organism will be grouped together into a single equivalence class.
                  This resulted in unusually many changes to equivalence classes.
                <p>
                  With Release 3.0, we modified the procedure for choosing the representative
                  of each equivalence class.
                  The representative is now chosen as the IFE (Integrated Functional Element)
                  which optimizes a combination of resolution, RSR, RSCC, Rfree, percent of nucleotides with steric clashes,
                  and the fraction of the molecule observed.
                  The intention is to select the structure
                  with the best experimental evidence for the coordinates being reported.
                  Details will be provided in an upcoming publication.
                </p>
                <p>
                  Individual chains are named in the format XXXX|M|C, where XXXX is the PDB entry, M is the model number,
                  usually 1, and C is the chain identifier, one to four characters.
                  IFEs are made up of individual chains linked with + signs.
                </p>
                <p>
                  With Release 2.0, we upgraded the BGSU RNA 3D Hub Site
                  to include new RNA 3D structures distributed in
                  <a href="http://wwpdb.org/news/news.php?year=2014#10-December-2014">mmCIF format</a>.
                  Some RNA-containing mmCIF structures are very large, containing multiple full ribosomes.
                  Most of the individual RNA molecules of interest occur as single covalently-bonded chains
                  (e.g., tRNA, small ribosomal subunit) but others occur as two or more chains that are
                  strongly coupled by persistent RNA basepairing (e.g., eukaryotic large ribosomal subunit with 5.8S RNA).
                  We refer to these single or coupled chains as Integrated Functional Elements (IFEs).
                  We extract IFEs from each 3D structure file, compare them to one another
                  by sequence and geometry, and group together those which share highly
                  similar sequence, geometry, and species, if known.
                  The groups are referred to as sequence/structure Equivalence Classes.
                  Before Release 3.0, the representative of each equivalence class
                  was chosen as the structure with the most annotated basepairs per nucleotide,
                  as a proxy for modeling quality.
                </p>
                <p>
                  Note that the representative sets were formerly referred to as
                  non-redundant lists, but in fact these lists have one instance of homologous
                  IFEs from each species, so they have some redundancy at the level of molecule.
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
