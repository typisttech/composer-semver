package main_test

import (
	"testing"

	"github.com/typisttech/composer-semver/internal"
)

//go:generate go run ./main.go
func Test(t *testing.T) {
	internal.RunTestscript(t)
}
