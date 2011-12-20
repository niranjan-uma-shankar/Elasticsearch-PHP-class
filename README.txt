This PHP class is to create and insert documents to an elasticsearch index, search based on a query and highlight matched results.

When you create an index, you need to remember that you need to set stemming, so that singular/plural matches of a word will return results. eg. for an indexed sentence like "I love programming challenges", a search query for "challenge" and for "challenges" will match this sentence and show it a a result only if stemming is setup, else a search for "challenge" will return no results (this will be undesirable in many cases).

To setup stemming, you can create the index like below:

curl -XPUT localhost:9200/my_index -d '
{
    "settings" : {
        "analysis" : {
            "analyzer" : {
                "stem" : {
                        "tokenizer" : "standard",
                        "filter" : ["standard", "lowercase", "stop", "porter_stem"]
                }
            }
        }
    },
    "mappings" : {
        "my_index_type" : {
            "dynamic" : true,
            "properties" : {
                "default" : {
                    "type" : "string",
                    "analyzer" : "stem"
                }
            }
        }
     }
}'

You will notice in the settings analyzer that the porter_stem filter has been set, along with a few other common filters like lowercase, stop etc. 
"analyzer" : {
                "stem" : {
                        "tokenizer" : "standard",
                        "filter" : ["standard", "lowercase", "stop", "porter_stem"]
                }
            }
In the mappings properties, the analyzer name is set in the "default" section, which will apply this analyzer to all fields in the index type my_index_type. You can also mention the fields where you want the analyzer to be applied to, by replacing "default" with the field name:
"properties" : {
                "my_field" : {
                    "type" : "string",
                    "analyzer" : "stem"
                }
            }

QUERYING:

<?php

require 'ElasticSearch.php';

/* For CodeIgniter users, you can also give: $this->config->load('elasticsearch'); */

$elasticSearch = new $this->elasticsearch;
$elasticSearch->index = 'my_index';
$elasticSearch->type = 'my_index_type';

//Remove lucene special characters from the query (reference: http://lucene.apache.org/java/2_9_1/queryparsersyntax.html#Escaping Special Characters)

$search_query = preg_replace('/(\+|\-|\&|\||\!|\(|\)|\{|\}|\[|\]|\^|\"|\~|\*|\?|\:|\\\\)/', ' ', $search_query);

//Remove unwanted spaces at the start and end of the query
$search_query = trim($query);

$results = array();
if (!$search_query) return $results;

$results = $elasticSearch->query($search_query);
$tags = $results->hits->hits;
foreach ($tags as $tag)
{
   //This stores the id of the matched results in an array and returns the array of matched ids. You can replace this part and do your own operation with the results here.
   $result = array($tag->_id);
   array_push($results,$result);
}
return $results;

OTHER TYPES OF QUERY:

In the example above, the number of search results that will be returned will be restricted to the default number. To specify a larger size, you can do this:
$size = 200; //Define your own value;
$results = $elasticSearch->query_wresultSize($search_query, $size=999)
---------------------------------------------------------------------------------------------------------------------
In the example above, the index type was defined, however if you have more than one index type and want the search to happen across all index types, you can replace the line where the index type was set with the one below:
$elasticSearch->index = '';

You can now use the query_all function to search over all index types:
$results = $elasticSearch->query_all($search_query);

You can define a size for the number of results to be returned:
$size = 100;
$results = $elasticSearch->query_all_wresultSize($search_query, $size);
---------------------------------------------------------------------------------------------------------------------
If you want the matched results to be highlighted, use the query_highlight function:

When you create the index, you need to set the term vector for fields in order for highlighting to work. This has certain advantages and you can read it up here: https://github.com/elasticsearch/elasticsearch/issues/585

"properties" : {
                "my_field" : {
                    "type" : "string",
                    "analyzer" : "stem",
                    "term_vector" : "with_positions_offsets"
                }
            }
Next:
$results = $elasticSearch->query_highlight($search_query);

Now let us take a look at the query_highlight function in the library. Take this line:
'content' => '{"highlight":{"fields":{"field_1":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]} ...

pre_tags and post_tags are optional and can be used to customize the styling of the highlighting that you need. In the above line, the matched results are shown in bold with a gray highlight bar. If the pre-post tags are not given, the matched results are wrapped in <em> </em> tags:
'content' => '{"highlight":{"fields":{"field_1":{}}}} ...

If you want to control the number of characters shown in the highlight fragment, and the number of fragments to be returned, you can add these values as per the syntax in the "Highlighted Fragments" section in this link: http://www.elasticsearch.org/guide/reference/api/search/highlighting.html

You would have also noticed that the field name to be highlighted is explicitly mentioned in the query_highlight function- field1, field2 etc. If you want the highlighting to happen across all fields, then map the _all field as stored when you create the index and change the query_highlight function as given below. However, this will result in a larger index size:

"properties" : {
                "my_field" : {
                    "type" : "string",
                    "analyzer" : "stem", 
                    "_all": {
                             "enabled": true,
                             "store": "yes"
                    }
                }
            }

in query_highlight function:
'content' => '{"highlight":{"fields":{"_all":{"pre_tags" : ["<b style=\"background-color:#C8C8C8\">"], "post_tags" : ["</b>"]}}}}'
