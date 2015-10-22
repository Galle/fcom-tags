<?php

add_action( 'pre_get_posts', function ($query ){
    global $wp;

    if ( !is_admin() && $query->is_main_query() ) {
        if ($wp->request == 'fcom-tags/json/data'){
        
            global $wpdb;
	        global $fcom_tags_db_version;

	        $table_name = $wpdb->prefix . 'fcom_tag_relations';
	        $terms_table_name = $wpdb->prefix. 'terms';
	        $term_table = $wpdb->prefix.'term_taxonomy';
	        
	        //$all_tags = new WP_Query(array('tax_query' => array('taxonomy'=>'post_tags')));
	        $all_tags = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id
                    FROM ".$term_table." 
                    WHERE taxonomy = 'post_tag'
                    )
	            ");
	        
	        $anclas = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id 
	                FROM ".$term_table." 
	                WHERE taxonomy = 'post_tag' 
	                )
	            AND term_id NOT IN (
	                SELECT tag_id FROM ".$table_name."
	                )
	                ");
	                
            $nodos_array = array();
            $tag_id= array();
            $tag_index = 0;
            $links_array = array();
            $grupo = 1;
            // $x = 16*cos($angulo);
            // $y = 9*sin($angulo);
            // deg2rad();
            $cantidad = count($anclas);
            $contador = 0;
            $angulo = 0;
            $escala = 200;
            
            
            foreach($anclas as $ancla)
            {
                $angulo = $contador*360/$cantidad;
                $nodos_array[] = array("name"=>$ancla->name, "group"=>$grupo,'x' =>$escala*8*cos(deg2rad(90+$angulo)), 'y' =>$escala*5*sin(deg2rad(90+$angulo)), 'classname' => 'ancla', 'fuerzas' => array());
                $tag_id[$ancla->term_id][] = array('index'=>$tag_index,'grupo'=>$grupo);
                $ancla_index = $tag_index;
                $tag_index++;
                
                
                $hijos_ancla = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id 
	                FROM ".$term_table." 
	                WHERE taxonomy = 'post_tag' 
	                )
	            AND term_id IN (
	                SELECT tag_id FROM ".$table_name." 
	                WHERE tag_padre_id = ".$ancla->term_id."
	                )
	                ");
                
                foreach($hijos_ancla as $tag)
                {
                    fcom_tags_recursive_node($tag_id, $links_array, $tag_index, $nodos_array, $tag, $ancla_index, $grupo);
                }
                
                $grupo++;
                $contador++;
            }
            
            //Articulos
            
            $args = array('post_type' => 'post');
            $post_query = new WP_Query($args);
            
            $x = 0; $y=0;
            
            if($post_query->have_posts() )
            {
                while($post_query->have_posts() )
                {
                    $post_query->the_post();
                    //Ver que grupo es el más frecuente
                    $grupo_count = array();
                    //Busca el tag y todas sus copias en las distintas anclas
                    $fuerzas_temp = array();
                    
                    $post_tags = get_tags();
                    foreach($post_tags as $tag)
                    {
                        if (array_key_exists($tag->term_id,$tag_id))
                        {
                            foreach($tag_id[$tag->term_id] as $semitag)
                            {
                                $fuerzas_temp[] = array('nodo' => $semitag['index'], 'grupo' => $semitag['grupo']);
                                if(array_key_exists($semitag['grupo'],$grupo_count))
                                {
                                    $grupo_count[$semitag['grupo']]+=1;
                                }
                                else
                                {
                                    $grupo_count[$semitag['grupo']]=1;
                                }
                                //$links_array[] = array("source" => $tag_index ,"target" => $semitag,'classname' => 'tag_articulo');
                            }
                        }
                    }
                    
                    $grupo_tag = 0;
                    $grupo_diff= 0;
                    if(count($grupo_count)>1)
                    {
                        $grupo_tag = array_keys($grupo_count, max($grupo_count));
                        arsort($grupo_count);
                        $keys = array_keys($grupo_count);
                        $grupo_diff= $grupo_count[$keys[0]] - $grupo_count[$keys[1]];
                    }
                    
                    //crea el nodo
                    $nodos_array[] = array(
                        "name"=>get_the_title(),
                        "group"=>$grupo,
                        'x' => $x, 'y' => $y, 
                        'classname' => 'articulo',
                        //'medio' => $articulo->getMedio(),
                        //'medioClass' => $articulo->getMedioClass(),  
                        'fuerzas' => $fuerzas_temp,
                        'titulo' => get_the_title(), 
                        //'bajada' => get_the_excerpt(),
                        //'img_path' => $articulo->getWebPath()
                        'path' => get_permalink(),
                        'grupo_tag' => $grupo_tag,
                        'grupo_weight' => 1.1+$grupo_diff/(count($post_tags)+1),
                        'fecha' => array('dia' => get_the_time('d'), 'mes'=> get_the_time('M'), 'agno' => get_the_time('Y'))
                        );
                    $tag_index++;
                    
                    
                    //posicion inicial pre convergencia
                    ($x+$y)%2==0 ? $x++ : $y++;
                }
            
            }   
            wp_reset_query();
            $retorno = array("nodes"=>$nodos_array,"links"=>$links_array);
            wp_send_json($retorno);
           
            wp_die();
            
            exit;
        }
    }
});


function fcom_tags_recursive_node(&$tag_id, &$links_array, &$tag_index, &$nodos_array, $tag, $padre, $grupo)
{
    global $wpdb;
    
    $tag_padre = $tag_index;
    $nodos_array[] = array("name"=>$tag->name,"group"=>$grupo, 'classname' => 'tag', 'fuerzas' => array());
    $tag_id[$tag->term_id][] = array('index'=>$tag_index,'grupo'=>$grupo);
    $links_array[] = array("source" => $padre ,"target" => $tag_index,"classname" => 'ancla_tag');
    $tag_index++; 
    
    $tags_hijos = $wpdb->get_results("
	            SELECT term_id, name
	            FROM ".$terms_table_name." 
	            WHERE term_id IN (
	                SELECT term_id 
	                FROM ".$term_table." 
	                WHERE taxonomy = 'post_tag' 
	                )
	            AND term_id IN (
	                SELECT tag_id FROM ".$table_name." 
	                WHERE tag_padre_id = ".$tag->term_id."
	                )
	                ");
    
    foreach($tags_hijos as $tagHijo)
    {
        fcom_tags_recursive_node($tag_id, $links_array, $tag_index, $nodos_array, $tagHijo, $tag_padre, $grupo);
    }
    
}