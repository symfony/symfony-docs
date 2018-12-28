workflow "Test" {
  on = "push"
  resolves = ["RST-Checker"]
}

action "RST-Checker" {
  uses = "docker://oskarstark/rst-checker"
  secrets = ["GITHUB_TOKEN"]
  env = {
    DOCS_DIR = "."
  }
}
