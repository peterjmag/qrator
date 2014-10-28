<?php
namespace spec\watoki\cqurator\fixtures;

use watoki\scrut\Fixture;

class ClassFixture extends Fixture {

    public function givenTheClass($fqn) {
        $this->givenTheClass_WithTheBody($fqn, '');
    }

    public function givenTheClass_WithTheBody($fqn, $body) {
        $parts = explode('\\', $fqn);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);

        $code = "namespace $namespace; class $name {
            $body
        }";
        $evald = eval($code);
        if (!$evald === false) {
            throw new \Exception("Could not eval: \n\n" . $code);
        }
    }

    public function then_ShouldBe($expression, $value) {
        $this->spec->assertEquals($value, eval("return $expression;"));
    }
}