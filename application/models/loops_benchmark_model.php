<?php
class Loops_benchmark_model extends CI_Model {

    function __construct()
    {
        $CI = & get_instance();

        // Call the Model constructor
        parent::__construct();
    }

    function make_checkbox($nums, $chain, $method, $i)
    {
        $nts = split(', ',$nums);
        foreach ($nts as &$nt) {
            $nt = implode('_', array('1S72_AU_1', $chain, $nt, ''));
        }
        $nums = implode(',', $nts);
        $id = $method . $i;
        return "<input type='radio' name='r' class='jmolInline' id='$id' data-nt='$nums'>";
    }

    function make_benchmark_cell($key, $value, $chain, $i)
    {
        $methods = array('fr3d','rna3dmotif','rnajunction','scor','cossmos','rloom');
        if (in_array($key, $methods)) {
            $value = str_replace(',',', ',$value);
            $chbx = $this->make_checkbox($value, $chain, $key, $i);
            if ($key == 'fr3d' and $value != '0' and $value != '1') {
                $span = "<span class='label success twipsy' title='$value'>$chbx found</span>";
            } elseif ($value == '0') {
                $span = "<span class='label important twipsy'>not found</span>";
            } elseif ($value == '1') {
                $span = "<span class='label success twipsy'>found</span>";
            } else {
                $span = "<span class='label warning twipsy' title='$value'>$chbx found</span>";
            }
            $s = array('data' => $span, 'class' => $key);
        } elseif ($key == 'manual_annotation') {
            $s = array('contentEditable'=>'true', 'class'=>'editable', 'data'=>$value);
        } elseif ($key == 'id') {
            $s = array('class'=>'ids', 'data'=>$value);
        } else {
            $s = $value;
        }
        return $s;
    }

    function get_benchmark_table($motif_type)
    {
        $chainbreaks = array('HL'=>'0', 'IL'=>'1', 'J3'=>'2');
        $this->db->select('id,chain,fr3d,rna3dmotif,scor,rloom,rnajunction,cossmos,manual_annotation')
                 ->from('__loop_benchmark')
                 ->where('type',$chainbreaks[$motif_type]);
        $query = $this->db->get();

        $i = 1;
        foreach ($query->result() as $row) {
            $chain = $row->chain;
            $table_row = array($i);
            $fields = get_object_vars($row);
            foreach ($fields as $key => $value) {
                $table_row[] = $this->make_benchmark_cell($key, $value, $chain, $i);
            }
            $table[] = $table_row;
            $i++;
        }

        return $table;
    }

}

/* End of file loops_benchmark_model.php */
/* Location: ./application/model/loops_benchmark_model.php */