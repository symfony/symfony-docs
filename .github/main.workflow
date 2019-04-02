workflow "Test" {
  on = "push"
  resolves = [
    "DOCtor-RST",
  ]
}

action "DOCtor-RST" {
  uses = "docker://oskarstark/doctor-rst"
  args = "--short"
}
