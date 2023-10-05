<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Pagination extends CI_Pagination
{
    public function getNumLinks()
    {
        return $this->num_links;
    }

    public function create_links_vuetify()
    {
        if ($this->total_rows == 0 or $this->per_page == 0) 
        {
            return '';
        }

        // Calculate the total number of pages
        $num_pages = (int)ceil($this->total_rows / $this->per_page);

		// Is there only one page? Hm... nothing more to do here then.
        if ($num_pages === 1) {
            return '';
        }

        // Are we using query strings?
        $this->cur_page = $this->cur_page + 1;
        if ($this->page_query_string === true) {
            $this->cur_page = $this->CI->input->get($this->query_string_segment) ? $this->CI->input->get($this->query_string_segment) : 1;
        }

        $html = sprintf(
            '<v-pagination circle
                color="success"
                :value="%s"
                :length="%s"
                total-visible="%s"
                @input="pagingChange"
            ></v-pagination>',
            $this->cur_page,
            $num_pages,
            $this->num_links
        );

        return $html;
    }
}