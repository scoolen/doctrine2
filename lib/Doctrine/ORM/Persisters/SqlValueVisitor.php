<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM\Persisters;

use Doctrine\Common\Collections\Expr\ExpressionVisitor;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Value;
use Doctrine\Common\Collections\Expr\CompositeExpression;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Types\Type;

/**
 * Extract the values from a criteria/expression
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class SqlValueVisitor extends ExpressionVisitor
{
    /**
     * @var array
     */
    private $values = array();

    /**
     * @var array
     */
    private $types  = array();

    /**
     * @var ClassMetadata
     */
    private $class;

    public function __cosntruct(ClassMetadata $class)
    {
        $this->class = $class;
    }

    public function walkComparison(Comparison $comparison)
    {
        $field          = $comparison->getField();
        $this->values[] = $comparison->getValue()->getValue();
        $this->types[]  = isset($this->class->fieldMappings[$field]) ?
                          Type::getType($this->class->fieldMappings[$field]['type'])->getBindingType() :
                          \PDO::PARAM_STR;
    }

    public function walkCompositeExpression(CompositeExpression $expr)
    {
        foreach ($expr->getExpressionList() as $child) {
            $this->dispatch($child);
        }
    }

    public function walkValue(Value $value)
    {
        return;
    }

    public function getParamsAndTypes()
    {
        return array($this->values, $this->types);
    }
}
