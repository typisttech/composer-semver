package internal_test

import (
	"os/exec"
	"testing"
)

func Test(t *testing.T) {
	p, err := exec.LookPath("composer-semver")
	if err != nil {
		t.Error("Could not find composer-semver binary in PATH")
		t.FailNow()
	}

	t.Logf("Found composer-semver binary at %s", p)
}
