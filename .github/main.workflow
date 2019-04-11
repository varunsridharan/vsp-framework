workflow "Deploy" {
   resolves = ["WP Pot Generator"]
   on = "push"
 }
action "WP Pot Generator" {
   uses = "varunsridharan/wordpress-pot-generator@master"
   env = {
      SAVE_PATH = "language/vsp-framework.pot" 
      ITEM_SLUG = "vsp-framework" 
      DOMAIN = "vsp-framework" 
      PACKAGE_NAME = "VSP Framework" 
      HEADERS = "{\"Report-Msgid-Bugs-To\":\"https://github.com/varunsridharan/vsp-framework/issues\",\"Last-Translator\":\"Varun Sridharan <varunsridharan23@gmail.com>\",\"Language-Team\":\"Varun Sridharan <varunsridharan23@gmail.com>\"}" 
   }
   secrets = ["GITHUB_TOKEN"]
}
