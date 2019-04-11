workflow "Deploy" {
  resolves = ["WP Pot Generator"]
  on = "push"
}

action "WP Pot Generator" {
  uses = "varunsridharan/wordpress-pot-generator@master"
  env = {
    ITEM_SLUG = "vsp-framework"
    DOMAIN = "vsp-framework"
    PACKAGE_NAME = "VSP Framework"
    HEADERS = "{\"Report-Msgid-Bugs-To\":\"https://github.com/varunsridharan/vsp-framework/issues\",\"Last-Translator\":\"Varun Sridharan <varunsridharan23@gmail.com>\",\"Language-Team\":\"Varun Sridharan <varunsridharan23@gmail.com>\"}"
    SAVE_PATH = "languages/vsp-framework.pot"
  }
  secrets = ["GITHUB_TOKEN"]
}
