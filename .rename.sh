find ./ -type f -exec sed -i -e 's|Disciple_Tools_Plugin_Starter_Template|Zume_Metrics|g' {} \;
find ./ -type f -exec sed -i -e 's|disciple_tools_plugin_starter_template|zume_metrics|g' {} \;
find ./ -type f -exec sed -i -e 's|disciple-tools-plugin-starter-template|zume-metrics|g' {} \;
find ./ -type f -exec sed -i -e 's|starter_post_type|zume_metrics|g' {} \;
find ./ -type f -exec sed -i -e 's|Plugin Starter Template|Zume Metrics|g' {} \;
mv disciple-tools-plugin-starter-template.php zume-metrics.php;
cd ..;
mv disciple-tools-plugin-starter-template zume-metrics;
