<?php

namespace Kambo\Asm;

class MethodWriter extends MethodVisitor
{
    public static $ACC_CONSTRUCTOR;  // int
    public static $SAME_FRAME;   // int
    public static $SAME_LOCALS_1_STACK_ITEM_FRAME;   // int
    public static $RESERVED; // int
    public static $SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED;  // int
    public static $CHOP_FRAME;   // int
    public static $SAME_FRAME_EXTENDED;  // int
    public static $APPEND_FRAME; // int
    public static $FULL_FRAME;   // int
    public static $FRAMES;   // int
    public static $INSERTED_FRAMES;  // int
    public static $MAXS; // int
    public static $NOTHING;  // int
    protected $cw;  // ClassWriter
    protected $access;  // int
    protected $name;    // int
    protected $desc;    // int
    protected $descriptor;  // String
    protected $signature;   // String
    protected $classReaderOffset;   // int
    protected $classReaderLength;   // int
    protected $exceptionCount;  // int
    protected $exceptions;  // int[]
    protected $annd;    // ByteVector
    protected $anns;    // AnnotationWriter
    protected $ianns;   // AnnotationWriter
    protected $tanns;   // AnnotationWriter
    protected $itanns;  // AnnotationWriter
    protected $panns;   // AnnotationWriter[]
    protected $ipanns;  // AnnotationWriter[]
    protected $synthetics;  // int
    protected $attrs;   // Attribute
    protected $code;    // ByteVector
    protected $maxStack;    // int
    protected $maxLocals;   // int
    protected $currentLocals;   // int
    protected $frameCount;  // int
    protected $stackMap;    // ByteVector
    protected $previousFrameOffset; // int
    protected $previousFrame;   // int[]
    protected $frame;   // int[]
    protected $handlerCount;    // int
    protected $firstHandler;    // Handler
    protected $lastHandler; // Handler
    protected $methodParametersCount;   // int
    protected $methodParameters;    // ByteVector
    protected $localVarCount;   // int
    protected $localVar;    // ByteVector
    protected $localVarTypeCount;   // int
    protected $localVarType;    // ByteVector
    protected $lineNumberCount; // int
    protected $lineNumber;  // ByteVector
    protected $lastCodeOffset;  // int
    protected $ctanns;  // AnnotationWriter
    protected $ictanns; // AnnotationWriter
    protected $cattrs;  // Attribute
    protected $subroutines; // int
    protected $compute; // int
    protected $labels;  // Label
    protected $previousBlock;   // Label
    protected $currentBlock;    // Label
    protected $stackSize;   // int
    protected $maxStackSize;    // int
    private function __init()
    {
 // default class members
        $this->code = new ByteVector();
    }
    public static function __staticinit()
    {
 // static class members
        self::$ACC_CONSTRUCTOR = 0x80000;
        self::$SAME_FRAME = 0;
        self::$SAME_LOCALS_1_STACK_ITEM_FRAME = 64;
        self::$RESERVED = 128;
        self::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED = 247;
        self::$CHOP_FRAME = 248;
        self::$SAME_FRAME_EXTENDED = 251;
        self::$APPEND_FRAME = 252;
        self::$FULL_FRAME = 255;
        self::$FRAMES = 0;
        self::$INSERTED_FRAMES = 1;
        self::$MAXS = 2;
        self::$NOTHING = 3;
    }
    public static function constructor__ClassWriter_I_String_String_String_aString_I($cw, $access, $name, $desc, $signature, $exceptions, $compute) // [final ClassWriter cw, final int access, final String name, final String desc, final String signature, final String[] exceptions, final int compute]
    {
        $me = new self();
        $me->__init();
        parent::constructor__I(Opcodes::ASM5);
        if (($cw->firstMethod == null)) {
            $cw->firstMethod = $me;
        } else {
            $cw->lastMethod->mv = $me;
        }
        $cw->lastMethod = $me;
        $me->cw = $cw;
        $me->access = $access;
        if ("<init>" /* from: "<init>".equals(name) */) {
            $me->access |= self::$ACC_CONSTRUCTOR;
        }
        $me->name = $cw->newUTF8($name);
        $me->desc = $cw->newUTF8($desc);
        $me->descriptor = $desc;

        if (ClassReader::SIGNATURES) {
            $me->signature = $signature;
        }

        if ((($exceptions != null) && (count($exceptions) /*from: exceptions.length*/ > 0))) {
            $me->exceptionCount = count($exceptions) /*from: exceptions.length*/;
            $me->exceptions = array();
            for ($i = 0; ($i < $me->exceptionCount); ++$i) {
                $me->exceptions[$i] = $cw->newClass($exceptions[$i]);
            }
        }
        $me->compute = $compute;
        if (($compute != self::$NOTHING)) {
            $size = ($Type->getArgumentsAndReturnSizes($me->descriptor) >> 2);
            if (((($access & Opcodes::ACC_STATIC)) != 0)) {
                --$size;
            }
            $me->maxLocals = $size;
            $me->currentLocals = $size;
            $me->labels = new Label();
            $me->labels->status |= $Label->PUSHED;
            $me->visitLabel($me->labels);
        }
        return $me;
    }
    public function visitParameter($name, $access) // [String name, int access]
    {
        if (($this->methodParameters == null)) {
            $this->methodParameters = new ByteVector();
        }
        ++$this->methodParametersCount;
        $this->methodParameters->putShort(( ((($name . null))) ? 0 : $this->cw->newUTF8($name) ))->putShort($access);
    }
    public function visitAnnotationDefault()
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $this->annd = new ByteVector();
        return new AnnotationWriter($this->cw, false, $this->annd, null, 0);
    }
    public function visitAnnotation($desc, $visible) // [final String desc, final boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, 2);
        if ($visible) {
            $aw->next = $this->anns;
            $this->anns = $aw;
        } else {
            $aw->next = $this->ianns;
            $this->ianns = $aw;
        }
        return $aw;
    }
    public function visitTypeAnnotation($typeRef, $typePath, $desc, $visible) // [final int typeRef, final TypePath typePath, final String desc, final boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        $AnnotationWriter->putTarget($typeRef, $typePath, $bv);
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
        if ($visible) {
            $aw->next = $this->tanns;
            $this->tanns = $aw;
        } else {
            $aw->next = $this->itanns;
            $this->itanns = $aw;
        }
        return $aw;
    }
    public function visitParameterAnnotation($parameter, $desc, $visible) // [final int parameter, final String desc, final boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        if ("Ljava/lang/Synthetic;" /* from: "Ljava/lang/Synthetic;".equals(desc) */) {
            $this->synthetics = max([$me->synthetics, ($parameter + 1)]);
            return new AnnotationWriter($this->cw, false, $bv, null, 0);
        }
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, 2);
        if ($visible) {
            if (($this->panns == null)) {
                $this->panns = array();
            }
            $aw->next = $this->panns[$parameter];
            $this->panns[$parameter] = $aw;
        } else {
            if (($this->ipanns == null)) {
                $this->ipanns = array();
            }
            $aw->next = $this->ipanns[$parameter];
            $this->ipanns[$parameter] = $aw;
        }
        return $aw;
    }
    public function visitAttribute($attr) // [final Attribute attr]
    {
        if ($attr->isCodeAttribute()) {
            $attr->next = $this->cattrs;
            $this->cattrs = $attr;
        } else {
            $attr->next = $this->attrs;
            $this->attrs = $attr;
        }
    }
    public function visitCode(){}
    public function visitFrame_I_I_aObject_I_aObject($type, $nLocal, $local, $nStack, $stack) // [final int type, final int nLocal, final Object[] local, final int nStack, final Object[] stack]
    {
        if ((!ClassReader::FRAMES || ($this->compute == self::$FRAMES))) {
            return ;
        }
        if (($this->compute == self::$INSERTED_FRAMES)) {
            if (($this->currentBlock->frame == null)) {
                $this->currentBlock->frame = new CurrentFrame();
                $this->currentBlock->frame->owner = $this->currentBlock;
                $this->currentBlock->frame->initInputFrame($this->cw, $this->access, $Type->getArgumentTypes($this->descriptor), $nLocal);
                $this->visitImplicitFirstFrame();
            } else {
                if (($type == Opcodes::F_NEW)) {
                    $this->currentBlock->frame->set($this->cw, $nLocal, $local, $nStack, $stack);
                } else {
            /* match: Frame */
                    $this->visitFrame_Frame($this->currentBlock->frame);
                }
            }
        } elseif (($type == Opcodes::F_NEW)) {
            if (($this->previousFrame == null)) {
                $this->visitImplicitFirstFrame();
            }
            $this->currentLocals = $nLocal;
            $frameIndex = $this->startFrame(count($this->code) /*from: code.length*/, $nLocal, $nStack);
            for ($i = 0; ($i < $nLocal); ++$i) {
                if ($local[$i] instanceof String) {
                    $this->frame[++$frameIndex] = ($Frame->OBJECT | $this->cw->addType_String($local[$i]));
                } elseif ($local[$i] instanceof Integer) {
                    $this->frame[++$frameIndex] = ($local[$i])->intValue();
                } else {
                    $this->frame[++$frameIndex] = ($Frame->UNINITIALIZED | $this->cw->addUninitializedType("", ($local[$i])::$position));
                }
            }
            for ($i = 0; ($i < $nStack); ++$i) {
                if ($stack[$i] instanceof String) {
                    $this->frame[++$frameIndex] = ($Frame->OBJECT | $this->cw->addType_String($stack[$i]));
                } elseif ($stack[$i] instanceof Integer) {
                    $this->frame[++$frameIndex] = ($stack[$i])->intValue();
                } else {
                    $this->frame[++$frameIndex] = ($Frame->UNINITIALIZED | $this->cw->addUninitializedType("", ($stack[$i])::$position));
                }
            }
            $this->endFrame();
        } else {
            $delta = null;
            if (($this->stackMap == null)) {
                $this->stackMap = new ByteVector();
                $delta = count($this->code) /*from: code.length*/;
            } else {
                $delta = ((count($this->code) /*from: code.length*/ - $this->previousFrameOffset) - 1);
                if (($delta < 0)) {
                    if (($type == Opcodes::F_SAME)) {
                        return ;
                    } else {
                        throw new IllegalStateException();
                    }
                }
            }
            switch ($type) {
                case Opcodes::F_FULL:
                    $this->currentLocals = $nLocal;
                    $this->stackMap->putByte(self::$FULL_FRAME)->putShort($delta)->putShort($nLocal);
                    for ($i = 0; ($i < $nLocal); ++$i) {
                        $this->writeFrameType($local[$i]);
                    }
                    $this->stackMap->putShort($nStack);
                    for ($i = 0; ($i < $nStack); ++$i) {
                        $this->writeFrameType($stack[$i]);
                    }
                    break;
                case Opcodes::F_APPEND:
                    $this->currentLocals += $nLocal;
                    $this->stackMap->putByte((self::$SAME_FRAME_EXTENDED + $nLocal))->putShort($delta);
                    for ($i = 0; ($i < $nLocal); ++$i) {
                        $this->writeFrameType($local[$i]);
                    }
                    break;
                case Opcodes::F_CHOP:
                    $this->currentLocals -= $nLocal;
                    $this->stackMap->putByte((self::$SAME_FRAME_EXTENDED - $nLocal))->putShort($delta);
                    break;
                case Opcodes::F_SAME:
                    if (($delta < 64)) {
                        $this->stackMap->putByte($delta);
                    } else {
                        $this->stackMap->putByte(self::$SAME_FRAME_EXTENDED)->putShort($delta);
                    }
                    break;
                case Opcodes::F_SAME1:
                    if (($delta < 64)) {
                        $this->stackMap->putByte((self::$SAME_LOCALS_1_STACK_ITEM_FRAME + $delta));
                    } else {
                        $this->stackMap->putByte(self::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED)->putShort($delta);
                    }
                    $this->writeFrameType($stack[0]);
                    break;
            }
            $this->previousFrameOffset = count($this->code) /*from: code.length*/;
            ++$this->frameCount;
        }
        $this->maxStack = max([$me->maxStack, $nStack]);
        $this->maxLocals = max([$me->maxLocals, $me->currentLocals]);
    }
    public function visitInsn($opcode) // [final int opcode]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $this->code->putByte($opcode);
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, 0, null, null);
            } else {
                $size = ($this->stackSize + $Frame->SIZE[$opcode]);
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
            if ((((($opcode >= Opcodes::IRETURN) && ($opcode <= Opcodes::RETURN_))) || ($opcode == Opcodes::ATHROW))) {
                $this->noSuccessor();
            }
        }
    }
    public function visitIntInsn($opcode, $operand) // [final int opcode, final int operand]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, $operand, null, null);
            } elseif (($opcode != Opcodes::NEWARRAY)) {
                $size = ($this->stackSize + 1);
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        if (($opcode == Opcodes::SIPUSH)) {
            $this->code->put12($opcode, $operand);
        } else {
            $this->code->put11($opcode, $operand);
        }
    }
    public function visitVarInsn($opcode, $var) // [final int opcode, final int var]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, $var, null, null);
            } else {
                if (($opcode == Opcodes::RET)) {
                    $this->currentBlock->status |= $Label->RET;
                    $this->currentBlock->inputStackTop = $this->stackSize;
                    $this->noSuccessor();
                } else {
                    $size = ($this->stackSize + $Frame->SIZE[$opcode]);
                    if (($size > $this->maxStackSize)) {
                        $this->maxStackSize = $size;
                    }
                    $this->stackSize = $size;
                }
            }
        }
        if (($this->compute != self::$NOTHING)) {
            $n = null;
            if ((((($opcode == Opcodes::LLOAD) || ($opcode == Opcodes::DLOAD)) || ($opcode == Opcodes::LSTORE)) || ($opcode == Opcodes::DSTORE))) {
                $n = ($var + 2);
            } else {
                $n = ($var + 1);
            }
            if (($n > $this->maxLocals)) {
                $this->maxLocals = $n;
            }
        }
        if ((($var < 4) && ($opcode != Opcodes::RET))) {
            $opt = null;
            if (($opcode < Opcodes::ISTORE)) {
                $opt = ((26 + (((($opcode - Opcodes::ILOAD)) << 2))) + $var);
            } else {
                $opt = ((59 + (((($opcode - Opcodes::ISTORE)) << 2))) + $var);
            }
            $this->code->putByte($opt);
        } elseif (($var >= 256)) {
            $this->code->putByte(196)->put12($opcode, $var);
        } else {
            $this->code->put11($opcode, $var);
        }
        if (((($opcode >= Opcodes::ISTORE) && ($this->compute == self::$FRAMES)) && ($this->handlerCount > 0))) {
            $this->visitLabel(new Label());
        }
    }
    public function visitTypeInsn($opcode, $type) // [final int opcode, final String type]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newClassItem($type);
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, count($this->code) /*from: code.length*/, $this->cw, $i);
            } elseif (($opcode == Opcodes::NEW_)) {
                $size = ($this->stackSize + 1);
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        $this->code->put12($opcode, $i->index);
    }
    public function visitFieldInsn($opcode, $owner, $name, $desc) // [final int opcode, final String owner, final String name, final String desc]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newFieldItem($owner, $name, $desc);
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, 0, $this->cw, $i);
            } else {
                $size = null;
                $c = $desc->charAt(0);
                switch ($opcode) {
                    case Opcodes::GETSTATIC:
                        $size = ($this->stackSize + (( ((($c . 'D') || ($c . 'J'))) ? 2 : 1 )));
                        break;
                    case Opcodes::PUTSTATIC:
                        $size = ($this->stackSize + (( ((($c . 'D') || ($c . 'J'))) ? -2 : -1 )));
                        break;
                    case Opcodes::GETFIELD:
                        $size = ($this->stackSize + (( ((($c . 'D') || ($c . 'J'))) ? 1 : 0 )));
                        break;
                    default:
                        $size = ($this->stackSize + (( ((($c . 'D') || ($c . 'J'))) ? -3 : -2 )));
                        break;
                }
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        $this->code->put12($opcode, $i->index);
    }
    public function visitMethodInsn($opcode, $owner, $name, $desc, $itf) // [final int opcode, final String owner, final String name, final String desc, final boolean itf]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newMethodItem($owner, $name, $desc, $itf);
        $argSize = $i->intVal;
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute($opcode, 0, $this->cw, $i);
            } else {
                if (($argSize == 0)) {
                    $argSize = $Type->getArgumentsAndReturnSizes($desc);
                    $i->intVal = $argSize;
                }
                $size = null;
                if (($opcode == Opcodes::INVOKESTATIC)) {
                    $size = ((($this->stackSize - (($argSize >> 2))) + (($argSize & 0x03))) + 1);
                } else {
                    $size = (($this->stackSize - (($argSize >> 2))) + (($argSize & 0x03)));
                }
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        if (($opcode == Opcodes::INVOKEINTERFACE)) {
            if (($argSize == 0)) {
                $argSize = $Type->getArgumentsAndReturnSizes($desc);
                $i->intVal = $argSize;
            }
            $this->code->put12(Opcodes::INVOKEINTERFACE, $i->index)->put11(($argSize >> 2), 0);
        } else {
            $this->code->put12($opcode, $i->index);
        }
    }
    public function visitInvokeDynamicInsn($name, $desc, $bsm, $bsmArgs) // [final String name, final String desc, final Handle bsm, final Object... bsmArgs]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newInvokeDynamicItem($name, $desc, $bsm, $bsmArgs);
        $argSize = $i->intVal;
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute(Opcodes::INVOKEDYNAMIC, 0, $this->cw, $i);
            } else {
                if (($argSize == 0)) {
                    $argSize = $Type->getArgumentsAndReturnSizes($desc);
                    $i->intVal = $argSize;
                }
                $size = ((($this->stackSize - (($argSize >> 2))) + (($argSize & 0x03))) + 1);
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        $this->code->put12(Opcodes::INVOKEDYNAMIC, $i->index);
        $this->code->putShort(0);
    }
    public function visitJumpInsn($opcode, $label) // [int opcode, final Label label]
    {
        $isWide = ($opcode >= 200);
        $opcode = ( ($isWide) ? ($opcode - 33) : $opcode );
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $nextInsn = null;
        if (($this->currentBlock != null)) {
            if (($this->compute == self::$FRAMES)) {
                $this->currentBlock->frame->execute($opcode, 0, null, null);
                $label->getFirst()->status |= $Label->TARGET;
                $this->addSuccessor($Edge->NORMAL, $label);
                if (($opcode != Opcodes::GOTO)) {
                    $nextInsn = new Label();
                }
            } elseif (($this->compute == self::$INSERTED_FRAMES)) {
                $this->currentBlock->frame->execute($opcode, 0, null, null);
            } else {
                if (($opcode == Opcodes::JSR)) {
                    if (((($label->status & $Label->SUBROUTINE)) == 0)) {
                        $label->status |= $Label->SUBROUTINE;
                        ++$this->subroutines;
                    }
                    $this->currentBlock->status |= $Label->JSR;
                    $this->addSuccessor(($this->stackSize + 1), $label);
                    $nextInsn = new Label();
                } else {
                    $this->stackSize += $Frame->SIZE[$opcode];
                    $this->addSuccessor($this->stackSize, $label);
                }
            }
        }
        if ((((($label->status & $Label->RESOLVED)) != 0) && (($label->position - count($this->code) /*from: code.length*/) < $Short->MIN_VALUE))) {
            if (($opcode == Opcodes::GOTO)) {
                $this->code->putByte(200);
            } elseif (($opcode == Opcodes::JSR)) {
                $this->code->putByte(201);
            } else {
                if (($nextInsn != null)) {
                    $nextInsn->status |= $Label->TARGET;
                }
                $this->code->putByte(( (($opcode <= 166)) ? ((((($opcode + 1)) ^ 1)) - 1) : ($opcode ^ 1) ));
                $this->code->putShort(8);
                $this->code->putByte(200);
            }
            $label->put($this, $this->code, (count($this->code) /*from: code.length*/ - 1), true);
        } elseif ($isWide) {
            $this->code->putByte(($opcode + 33));
            $label->put($this, $this->code, (count($this->code) /*from: code.length*/ - 1), true);
        } else {
            $this->code->putByte($opcode);
            $label->put($this, $this->code, (count($this->code) /*from: code.length*/ - 1), false);
        }
        if (($this->currentBlock != null)) {
            if (($nextInsn != null)) {
                $this->visitLabel($nextInsn);
            }
            if (($opcode == Opcodes::GOTO)) {
                $this->noSuccessor();
            }
        }
    }
    public function visitLabel($label) // [final Label label]
    {
        $this->cw->hasAsmInsns |= $label->resolve($this, count($this->code) /*from: code.length*/, $this->code->data);
        if (((($label->status & $Label->DEBUG)) != 0)) {
            return ;
        }
        if (($this->compute == self::$FRAMES)) {
            if (($this->currentBlock != null)) {
                if (($label->position == $this->currentBlock->position)) {
                    $this->currentBlock->status |= (($label->status & $Label->TARGET));
                    $label->frame = $this->currentBlock->frame;
                    return ;
                }
                $this->addSuccessor($Edge->NORMAL, $label);
            }
            $this->currentBlock = $label;
            if (($label->frame == null)) {
                $label->frame = new Frame();
                $label->frame->owner = $label;
            }
            if (($this->previousBlock != null)) {
                if (($label->position == $this->previousBlock->position)) {
                    $this->previousBlock->status |= (($label->status & $Label->TARGET));
                    $label->frame = $this->previousBlock->frame;
                    $this->currentBlock = $this->previousBlock;
                    return ;
                }
                $this->previousBlock->successor = $label;
            }
            $this->previousBlock = $label;
        } elseif (($this->compute == self::$INSERTED_FRAMES)) {
            if (($this->currentBlock == null)) {
                $this->currentBlock = $label;
            } else {
                $this->currentBlock->frame->owner = $label;
            }
        } elseif (($this->compute == self::$MAXS)) {
            if (($this->currentBlock != null)) {
                $this->currentBlock->outputStackMax = $this->maxStackSize;
                $this->addSuccessor($this->stackSize, $label);
            }
            $this->currentBlock = $label;
            $this->stackSize = 0;
            $this->maxStackSize = 0;
            if (($this->previousBlock != null)) {
                $this->previousBlock->successor = $label;
            }
            $this->previousBlock = $label;
        }
    }
    public function visitLdcInsn($cst) // [final Object cst]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newConstItem($cst);
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute(Opcodes::LDC, 0, $this->cw, $i);
            } else {
                $size = null;
                if ((($i->type == ClassWriter::$LONG) || ($i->type == ClassWriter::$DOUBLE))) {
                    $size = ($this->stackSize + 2);
                } else {
                    $size = ($this->stackSize + 1);
                }
                if (($size > $this->maxStackSize)) {
                    $this->maxStackSize = $size;
                }
                $this->stackSize = $size;
            }
        }
        $index = $i->index;
        if ((($i->type == ClassWriter::$LONG) || ($i->type == ClassWriter::$DOUBLE))) {
            $this->code->put12(20, $index);
        } elseif (($index >= 256)) {
            $this->code->put12(19, $index);
        } else {
            $this->code->put11(Opcodes::LDC, $index);
        }
    }
    public function visitIincInsn($var, $increment) // [final int var, final int increment]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute(Opcodes::IINC, $var, null, null);
            }
        }
        if (($this->compute != self::$NOTHING)) {
            $n = ($var + 1);
            if (($n > $this->maxLocals)) {
                $this->maxLocals = $n;
            }
        }
        if ((((($var > 255)) || (($increment > 127))) || (($increment < -128)))) {
            $this->code->putByte(196)->put12(Opcodes::IINC, $var)->putShort($increment);
        } else {
            $this->code->putByte(Opcodes::IINC)->put11($var, $increment);
        }
    }
    public function visitTableSwitchInsn($min, $max, $dflt, $labels) // [final int min, final int max, final Label dflt, final Label... labels]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $source = count($this->code) /*from: code.length*/;
        $this->code->putByte(Opcodes::TABLESWITCH);
        $this->code->putByteArray(null, 0, (((4 - (count($this->code) /*from: code.length*/ % 4))) % 4));
        $dflt->put($this, $this->code, $source, true);
        $this->code->putInt($min)->putInt($max);
        for ($i = 0; ($i < count($labels) /*from: labels.length*/); ++$i) {
            $labels[$i]->put($this, $this->code, $source, true);
        }
        $this->visitSwitchInsn($dflt, $labels);
    }
    public function visitLookupSwitchInsn($dflt, $keys, $labels) // [final Label dflt, final int[] keys, final Label[] labels]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $source = count($this->code) /*from: code.length*/;
        $this->code->putByte(Opcodes::LOOKUPSWITCH);
        $this->code->putByteArray(null, 0, (((4 - (count($this->code) /*from: code.length*/ % 4))) % 4));
        $dflt->put($this, $this->code, $source, true);
        $this->code->putInt(count($labels) /*from: labels.length*/);
        for ($i = 0; ($i < count($labels) /*from: labels.length*/); ++$i) {
            $this->code->putInt($keys[$i]);
            $labels[$i]->put($this, $this->code, $source, true);
        }
        $this->visitSwitchInsn($dflt, $labels);
    }
    protected function visitSwitchInsn($dflt, $labels) // [final Label dflt, final Label[] labels]
    {
        if (($this->currentBlock != null)) {
            if (($this->compute == self::$FRAMES)) {
                $this->currentBlock->frame->execute(Opcodes::LOOKUPSWITCH, 0, null, null);
                $this->addSuccessor($Edge->NORMAL, $dflt);
                $dflt->getFirst()->status |= $Label->TARGET;
                for ($i = 0; ($i < count($labels) /*from: labels.length*/); ++$i) {
                    $this->addSuccessor($Edge->NORMAL, $labels[$i]);
                    $labels[$i]->getFirst()->status |= $Label->TARGET;
                }
            } else {
                --$this->stackSize;
                $this->addSuccessor($this->stackSize, $dflt);
                for ($i = 0; ($i < count($labels) /*from: labels.length*/); ++$i) {
                    $this->addSuccessor($this->stackSize, $labels[$i]);
                }
            }
            $this->noSuccessor();
        }
    }
    public function visitMultiANewArrayInsn($desc, $dims) // [final String desc, final int dims]
    {
        $this->lastCodeOffset = count($this->code) /*from: code.length*/;
        $i = $this->cw->newClassItem($desc);
        if (($this->currentBlock != null)) {
            if ((($this->compute == self::$FRAMES) || ($this->compute == self::$INSERTED_FRAMES))) {
                $this->currentBlock->frame->execute(Opcodes::MULTIANEWARRAY, $dims, $this->cw, $i);
            } else {
                $this->stackSize += (1 - $dims);
            }
        }
        $this->code->put12(Opcodes::MULTIANEWARRAY, $i->index)->putByte($dims);
    }
    public function visitInsnAnnotation($typeRef, $typePath, $desc, $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        $typeRef = ((($typeRef & 0xFF0000FF)) | (($this->lastCodeOffset << 8)));
        $AnnotationWriter->putTarget($typeRef, $typePath, $bv);
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
        if ($visible) {
            $aw->next = $this->ctanns;
            $this->ctanns = $aw;
        } else {
            $aw->next = $this->ictanns;
            $this->ictanns = $aw;
        }
        return $aw;
    }
    public function visitTryCatchBlock($start, $end, $handler, $type) // [final Label start, final Label end, final Label handler, final String type]
    {
        ++$this->handlerCount;
        $h = new Handler();
        $h->start = $start;
        $h->end = $end;
        $h->handler = $handler;
        $h->desc = $type;
        $h->type = ( (($type . null)) ? $this->cw->newClass($type) : 0 );
        if (($this->lastHandler == null)) {
            $this->firstHandler = $h;
        } else {
            $this->lastHandler->next = $h;
        }
        $this->lastHandler = $h;
    }
    public function visitTryCatchAnnotation($typeRef, $typePath, $desc, $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        $AnnotationWriter->putTarget($typeRef, $typePath, $bv);
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
        if ($visible) {
            $aw->next = $this->ctanns;
            $this->ctanns = $aw;
        } else {
            $aw->next = $this->ictanns;
            $this->ictanns = $aw;
        }
        return $aw;
    }
    public function visitLocalVariable($name, $desc, $signature, $start, $end, $index) // [final String name, final String desc, final String signature, final Label start, final Label end, final int index]
    {
        if (($signature . null)) {
            if (($this->localVarType == null)) {
                $this->localVarType = new ByteVector();
            }
            ++$this->localVarTypeCount;
            $this->localVarType->putShort($start->position)->putShort(($end->position - $start->position))->putShort($this->cw->newUTF8($name))->putShort($this->cw->newUTF8($signature))->putShort($index);
        }
        if (($this->localVar == null)) {
            $this->localVar = new ByteVector();
        }
        ++$this->localVarCount;
        $this->localVar->putShort($start->position)->putShort(($end->position - $start->position))->putShort($this->cw->newUTF8($name))->putShort($this->cw->newUTF8($desc))->putShort($index);
        if (($this->compute != self::$NOTHING)) {
            $c = $desc->charAt(0);
            $n = ($index + (( ((($c . 'J') || ($c . 'D'))) ? 2 : 1 )));
            if (($n > $this->maxLocals)) {
                $this->maxLocals = $n;
            }
        }
    }

        private function uRShift($a, $b)
{
    if($b == 0) return $a;
    return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
}

    public function visitLocalVariableAnnotation($typeRef, $typePath, $start, $end, $index, $desc, $visible) // [int typeRef, TypePath typePath, Label[] start, Label[] end, int[] index, String desc, boolean visible]
    {
        if (!ClassReader::ANNOTATIONS) {
            return null;
        }
        $bv = new ByteVector();
        $bv->putByte($this->uRShift($typeRef, 24))->putShort(count($start) /*from: start.length*/);
        for ($i = 0; ($i < count($start) /*from: start.length*/); ++$i) {
            $bv->putShort($start[$i]->position)->putShort(($end[$i]->position - $start[$i]->position))->putShort($index[$i]);
        }
        if (($typePath == null)) {
            $bv->putByte(0);
        } else {
            $length = (($typePath->b[$typePath->offset] * 2) + 1);
            $bv->putByteArray($typePath->b, $typePath->offset, $length);
        }
        $bv->putShort($this->cw->newUTF8($desc))->putShort(0);
        $aw = new AnnotationWriter($this->cw, true, $bv, $bv, (count($bv) /*from: bv.length*/ - 2));
        if ($visible) {
            $aw->next = $this->ctanns;
            $this->ctanns = $aw;
        } else {
            $aw->next = $this->ictanns;
            $this->ictanns = $aw;
        }
        return $aw;
    }
    public function visitLineNumber($line, $start) // [final int line, final Label start]
    {
        if (($this->lineNumber == null)) {
            $this->lineNumber = new ByteVector();
        }
        ++$this->lineNumberCount;
        $this->lineNumber->putShort($start->position);
        $this->lineNumber->putShort($line);
    }
    public function visitMaxs($maxStack, $maxLocals) // [final int maxStack, final int maxLocals]
    {
        if ((ClassReader::FRAMES && ($this->compute == self::$FRAMES))) {
            $handler = $this->firstHandler;
            while (($handler != null)) {
                $l = $handler->start->getFirst();
                $h = $handler->handler->getFirst();
                $e = $handler->end->getFirst();
                $t = ( (($handler->desc == null)) ? "java/lang/Throwable" : $handler->desc );
                $kind = ($Frame->OBJECT | $this->cw->addType_String($t));
                $h->status |= $Label->TARGET;
                while (($l != $e)) {
                    $b = new Edge();
                    $b->info = $kind;
                    $b->successor = $h;
                    $b->next = $l->successors;
                    $l->successors = $b;
                    $l = $l->successor;
                }
                $handler = $handler->next;
            }
            $f = $this->labels->frame;
            $f->initInputFrame($this->cw, $this->access, $Type->getArgumentTypes($this->descriptor), $this->maxLocals);
            /* match: Frame */
            $this->visitFrame_Frame($f);
            $max = 0;
            $changed = $this->labels;
            while (($changed != null)) {
                $l = $changed;
                $changed = $changed->next;
                $l->next = null;
                $f = $l->frame;
                if (((($l->status & $Label->TARGET)) != 0)) {
                    $l->status |= $Label->STORE;
                }
                $l->status |= $Label->REACHABLE;
                $blockMax = (count($f->inputStack) /*from: f.inputStack.length*/ + $l->outputStackMax);
                if (($blockMax > $max)) {
                    $max = $blockMax;
                }
                $e = $l->successors;
                while (($e != null)) {
                    $n = $e->successor->getFirst();
                    $change = $f->merge($this->cw, $n->frame, $e->info);
                    if (($change && ($n->next == null))) {
                        $n->next = $changed;
                        $changed = $n;
                    }
                    $e = $e->next;
                }
            }
            $l = $this->labels;
            while (($l != null)) {
                $f = $l->frame;
                if (((($l->status & $Label->STORE)) != 0)) {
            /* match: Frame */
                    $this->visitFrame_Frame($f);
                }
                if (((($l->status & $Label->REACHABLE)) == 0)) {
                    $k = $l->successor;
                    $start = $l->position;
                    $end = ((( (($k == null)) ? count($this->code) /*from: code.length*/ : $k->position )) - 1);
                    if (($end >= $start)) {
                        $max = max([$max, 1]);
                        for ($i = $start; ($i < $end); ++$i) {
                            $this->code->data[$i] = Opcodes::NOP;
                        }
                        $this->code->data[$end] = Opcodes::ATHROW;
                        $frameIndex = $this->startFrame($start, 0, 1);
                        $this->frame[$frameIndex] = ($Frame->OBJECT | $this->cw->addType_String("java/lang/Throwable"));
                        $this->endFrame();
                        $this->firstHandler = $Handler->remove($this->firstHandler, $l, $k);
                    }
                }
                $l = $l->successor;
            }
            $handler = $this->firstHandler;
            $this->handlerCount = 0;
            while (($handler != null)) {
                $this->handlerCount += 1;
                $handler = $handler->next;
            }
            $this->maxStack = $max;
        } elseif (($this->compute == self::$MAXS)) {
            $handler = $this->firstHandler;
            while (($handler != null)) {
                $l = $handler->start;
                $h = $handler->handler;
                $e = $handler->end;
                while (($l != $e)) {
                    $b = new Edge();
                    $b->info = $Edge->EXCEPTION;
                    $b->successor = $h;
                    if (((($l->status & $Label->JSR)) == 0)) {
                        $b->next = $l->successors;
                        $l->successors = $b;
                    } else {
                        $b->next = $l->successors->next->next;
                        $l->successors->next->next = $b;
                    }
                    $l = $l->successor;
                }
                $handler = $handler->next;
            }
            if (($this->subroutines > 0)) {
                $id = 0;
                $this->labels->visitSubroutine(null, 1, $this->subroutines);
                $l = $this->labels;
                while (($l != null)) {
                    if (((($l->status & $Label->JSR)) != 0)) {
                        $subroutine = $l->successors->next->successor;
                        if (((($subroutine->status & $Label->VISITED)) == 0)) {
                            $id += 1;
                            $subroutine->visitSubroutine(null, (((($id / 32)) << 32) | ((1 << (($id % 32))))), $this->subroutines);
                        }
                    }
                    $l = $l->successor;
                }
                $l = $this->labels;
                while (($l != null)) {
                    if (((($l->status & $Label->JSR)) != 0)) {
                        $L = $this->labels;
                        while (($L != null)) {
                            $L->status &= ~$Label->VISITED2;
                            $L = $L->successor;
                        }
                        $subroutine = $l->successors->next->successor;
                        $subroutine->visitSubroutine($l, 0, $this->subroutines);
                    }
                    $l = $l->successor;
                }
            }
            $max = 0;
            $stack = $this->labels;
            while (($stack != null)) {
                $l = $stack;
                $stack = $stack->next;
                $start = $l->inputStackTop;
                $blockMax = ($start + $l->outputStackMax);
                if (($blockMax > $max)) {
                    $max = $blockMax;
                }
                $b = $l->successors;
                if (((($l->status & $Label->JSR)) != 0)) {
                    $b = $b->next;
                }
                while (($b != null)) {
                    $l = $b->successor;
                    if (((($l->status & $Label->PUSHED)) == 0)) {
                        $l->inputStackTop = ( (($b->info == $Edge->EXCEPTION)) ? 1 : ($start + $b->info) );
                        $l->status |= $Label->PUSHED;
                        $l->next = $stack;
                        $stack = $l;
                    }
                    $b = $b->next;
                }
            }
            $this->maxStack = max([$maxStack, $max]);
        } else {
            $this->maxStack = $maxStack;
            $this->maxLocals = $maxLocals;
        }
    }
    public function visitEnd() {}
    protected function addSuccessor($info, $successor) // [final int info, final Label successor]
    {
        $b = new Edge();
        $b->info = $info;
        $b->successor = $successor;
        $b->next = $this->currentBlock->successors;
        $this->currentBlock->successors = $b;
    }
    protected function noSuccessor()
    {
        if (($this->compute == self::$FRAMES)) {
            $l = new Label();
            $l->frame = new Frame();
            $l->frame->owner = $l;
            $l->resolve($this, count($this->code) /*from: code.length*/, $this->code->data);
            $this->previousBlock->successor = $l;
            $this->previousBlock = $l;
        } else {
            $this->currentBlock->outputStackMax = $this->maxStackSize;
        }
        if (($this->compute != self::$INSERTED_FRAMES)) {
            $this->currentBlock = null;
        }
    }
    protected function visitFrame_Frame($f) // [final Frame f]
    {
        $i = null;
        $t = null;
        $nTop = 0;
        $nLocal = 0;
        $nStack = 0;
        $locals = $f->inputLocals;
        $stacks = $f->inputStack;
        for ($i = 0; ($i < count($locals) /*from: locals.length*/); ++$i) {
            $t = $locals[$i];
            if (($t == $Frame->TOP)) {
                ++$nTop;
            } else {
                $nLocal += ($nTop + 1);
                $nTop = 0;
            }
            if ((($t == $Frame->LONG) || ($t == $Frame->DOUBLE))) {
                ++$i;
            }
        }
        for ($i = 0; ($i < count($stacks) /*from: stacks.length*/); ++$i) {
            $t = $stacks[$i];
            ++$nStack;
            if ((($t == $Frame->LONG) || ($t == $Frame->DOUBLE))) {
                ++$i;
            }
        }
        $frameIndex = $this->startFrame($f->owner->position, $nLocal, $nStack);
        for ($i = 0; ($nLocal > 0); ++$i, --$nLocal) {
            $t = $locals[$i];
            $this->frame[++$frameIndex] = $t;
            if ((($t == $Frame->LONG) || ($t == $Frame->DOUBLE))) {
                ++$i;
            }
        }
        for ($i = 0; ($i < count($stacks) /*from: stacks.length*/); ++$i) {
            $t = $stacks[$i];
            $this->frame[++$frameIndex] = $t;
            if ((($t == $Frame->LONG) || ($t == $Frame->DOUBLE))) {
                ++$i;
            }
        }
        $this->endFrame();
    }

private function charAt($str, $pos)
{
  return $str{$pos};
}

    protected function visitImplicitFirstFrame()
    {
        $frameIndex = $this->startFrame(0, ($this->descriptor->length() + 1), 0);
        if (((($this->access & Opcodes::ACC_STATIC)) == 0)) {
            if (((($this->access & self::ACC_CONSTRUCTOR)) == 0)) {
                $this->frame[++$frameIndex] = ($Frame->OBJECT | $this->cw->addType_Item($this->cw->thisName));
            } else {
                $this->frame[++$frameIndex] = 6;
            }
        }
        $i = 1;

        while (true) {
            $j = $i;
            switch ($this->charAt($descriptor, $i++)) {
                case 'Z':
                case 'C':
                case 'B':
                case 'S':
                case 'I':
                    $this->frame[$frameIndex++] = 1;

                    break;
                case 'F':
                    $this->frame[$frameIndex++] = 2;

                    break;
                case 'J':
                    $this->frame[$frameIndex++] = 4;

                    break;
                case 'D':
                    $this->frame[$frameIndex++] = 3;

                    break;
                case '[':
                    while ($this->charAt($descriptor, $i) == '[') {
                        ++$i;
                    }

                    if ($this->charAt($descriptor, $i) == 'L') {
                        ++$i;
                        while ($this->charAt($descriptor, $i) != ';') {
                            ++$i;
                        }
                    }

                    $frame[$frameIndex++] = Frame::OBJECT | $this->cw->addType(substr($descriptor, $j, ++$i));

                    break;
                case 'L':
                    while ($this->charAt($descriptor, $i) != ';') {
                        ++$i;
                    }

                    $frame[$frameIndex++] = Frame::OBJECT | $this->cw->addType(substr($descriptor, $j+1, $i++) );

                    break;
                default:
                    break 2;
            }
        }
        
        $this->frame[1] = ($frameIndex - 3);
        $this->endFrame();
    }
    protected function startFrame($offset, $nLocal, $nStack) // [final int offset, final int nLocal, final int nStack]
    {
        $n = ((3 + $nLocal) + $nStack);
        if ((($this->frame == null) || (count($this->frame) /*from: frame.length*/ < $n))) {
            $this->frame = array();
        }
        $this->frame[0] = $offset;
        $this->frame[1] = $nLocal;
        $this->frame[2] = $nStack;
        return 3;
    }
    protected function endFrame()
    {
        if (($this->previousFrame != null)) {
            if (($this->stackMap == null)) {
                $this->stackMap = new ByteVector();
            }
            $this->writeFrame();
            ++$this->frameCount;
        }
        $this->previousFrame = $this->frame;
        $this->frame = null;
    }
    protected function writeFrame()
    {
        $clocalsSize = $this->frame[1];
        $cstackSize = $this->frame[2];
        if (((($this->cw->version & 0xFFFF)) < Opcodes::V1_6)) {
            $this->stackMap->putShort($this->frame[0])->putShort($clocalsSize);
            $this->writeFrameTypes(3, (3 + $clocalsSize));
            $this->stackMap->putShort($cstackSize);
            $this->writeFrameTypes((3 + $clocalsSize), ((3 + $clocalsSize) + $cstackSize));
            return ;
        }
        $localsSize = $this->previousFrame[1];
        $type = self::$FULL_FRAME;
        $k = 0;
        $delta = null;
        if (($this->frameCount == 0)) {
            $delta = $this->frame[0];
        } else {
            $delta = (($this->frame[0] - $this->previousFrame[0]) - 1);
        }
        if (($cstackSize == 0)) {
            $k = ($clocalsSize - $localsSize);
            switch ($k) {
                case -3:
                case -2:
                case -1:
                    $type = self::$CHOP_FRAME;
                    $localsSize = $clocalsSize;
                    break;
                case 0:
                    $type = ( (($delta < 64)) ? self::$SAME_FRAME : self::$SAME_FRAME_EXTENDED );
                    break;
                case 1:
                case 2:
                case 3:
                    $type = self::$APPEND_FRAME;
                    break;
            }
        } elseif ((($clocalsSize == $localsSize) && ($cstackSize == 1))) {
            $type = ( (($delta < 63)) ? self::$SAME_LOCALS_1_STACK_ITEM_FRAME : self::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED );
        }
        if (($type != self::$FULL_FRAME)) {
            $l = 3;
            for ($j = 0; ($j < $localsSize); ++$j) {
                if (($this->frame[$l] != $this->previousFrame[$l])) {
                    $type = self::$FULL_FRAME;
                    break;
                }
                ++$l;
            }
        }
        switch ($type) {
            case self::$SAME_FRAME:
                $this->stackMap->putByte($delta);
                break;
            case self::$SAME_LOCALS_1_STACK_ITEM_FRAME:
                $this->stackMap->putByte((self::$SAME_LOCALS_1_STACK_ITEM_FRAME + $delta));
                $this->writeFrameTypes((3 + $clocalsSize), (4 + $clocalsSize));
                break;
            case self::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED:
                $this->stackMap->putByte(self::$SAME_LOCALS_1_STACK_ITEM_FRAME_EXTENDED)->putShort($delta);
                $this->writeFrameTypes((3 + $clocalsSize), (4 + $clocalsSize));
                break;
            case self::$SAME_FRAME_EXTENDED:
                $this->stackMap->putByte(self::$SAME_FRAME_EXTENDED)->putShort($delta);
                break;
            case self::$CHOP_FRAME:
                $this->stackMap->putByte((self::$SAME_FRAME_EXTENDED + $k))->putShort($delta);
                break;
            case self::$APPEND_FRAME:
                $this->stackMap->putByte((self::$SAME_FRAME_EXTENDED + $k))->putShort($delta);
                $this->writeFrameTypes((3 + $localsSize), (3 + $clocalsSize));
                break;
            default:
                $this->stackMap->putByte(self::$FULL_FRAME)->putShort($delta)->putShort($clocalsSize);
                $this->writeFrameTypes(3, (3 + $clocalsSize));
                $this->stackMap->putShort($cstackSize);
                $this->writeFrameTypes((3 + $clocalsSize), ((3 + $clocalsSize) + $cstackSize));
        }
    }
    protected function writeFrameTypes($start, $end) // [final int start, final int end]
    {
        for ($i = $start; ($i < $end); ++$i) {
            $t = $this->frame[$i];
            $d = ($t & $Frame->DIM);
            if (($d == 0)) {
                $v = ($t & $Frame->BASE_VALUE);
                switch (($t & $Frame->BASE_KIND)) {
                    case $Frame->OBJECT:
                        $this->stackMap->putByte(7)->putShort($this->cw->newClass($this->cw->typeTable[$v]->strVal1));
                        break;
                    case $Frame->UNINITIALIZED:
                        $this->stackMap->putByte(8)->putShort($this->cw->typeTable[$v]->intVal);
                        break;
                    default:
                        $this->stackMap->putByte($v);
                }
            } else {
                $sb = new StringBuilder();
                $d >>= 28;
                while ((--$d > 0)) {
                    $sb->append('[');
                }
                if (((($t & $Frame->BASE_KIND)) == $Frame->OBJECT)) {
                    $sb->append('L');
                    $sb->append($this->cw->typeTable[($t & $Frame->BASE_VALUE)]->strVal1);
                    $sb->append(';');
                } else {
                    switch (($t & 0xF)) {
                        case 1:
                            $sb->append('I');
                            break;
                        case 2:
                            $sb->append('F');
                            break;
                        case 3:
                            $sb->append('D');
                            break;
                        case 9:
                            $sb->append('Z');
                            break;
                        case 10:
                            $sb->append('B');
                            break;
                        case 11:
                            $sb->append('C');
                            break;
                        case 12:
                            $sb->append('S');
                            break;
                        default:
                            $sb->append('J');
                    }
                }
                $this->stackMap->putByte(7)->putShort($this->cw->newClass($sb->toString()));
            }
        }
    }
    protected function writeFrameType($type) // [final Object type]
    {
        if ($type instanceof String) {
            $this->stackMap->putByte(7)->putShort($this->cw->newClass($type));
        } elseif ($type instanceof Integer) {
            $this->stackMap->putByte(($type)->intValue());
        } else {
            $this->stackMap->putByte(8)->putShort(($type)::$position);
        }
    }
    public function getSize()
    {
        if (($this->classReaderOffset != 0)) {
            return (6 + $this->classReaderLength);
        }
        $size = 8;
        if ((count($this->code) /*from: code.length*/ > 0)) {
            if ((count($this->code) /*from: code.length*/ > 65535)) {
                throw new RuntimeException("Method code too large!");
            }
            $this->cw->newUTF8("Code");
            $size += ((18 + count($this->code) /*from: code.length*/) + (8 * $this->handlerCount));
            if (($this->localVar != null)) {
                $this->cw->newUTF8("LocalVariableTable");
                $size += (8 + count($this->localVar) /*from: localVar.length*/);
            }
            if (($this->localVarType != null)) {
                $this->cw->newUTF8("LocalVariableTypeTable");
                $size += (8 + count($this->localVarType) /*from: localVarType.length*/);
            }
            if (($this->lineNumber != null)) {
                $this->cw->newUTF8("LineNumberTable");
                $size += (8 + count($this->lineNumber) /*from: lineNumber.length*/);
            }
            if (($this->stackMap != null)) {
                $zip = ((($this->cw->version & 0xFFFF)) >= Opcodes::V1_6);
                $this->cw->newUTF8(( ($zip) ? "StackMapTable" : "StackMap" ));
                $size += (8 + count($this->stackMap) /*from: stackMap.length*/);
            }
            if ((ClassReader::ANNOTATIONS && ($this->ctanns != null))) {
                $this->cw->newUTF8("RuntimeVisibleTypeAnnotations");
                $size += (8 + $this->ctanns->getSize());
            }
            if ((ClassReader::ANNOTATIONS && ($this->ictanns != null))) {
                $this->cw->newUTF8("RuntimeInvisibleTypeAnnotations");
                $size += (8 + $this->ictanns->getSize());
            }
            if (($this->cattrs != null)) {
                $size += $this->cattrs->getSize($this->cw, $this->code->data, count($this->code) /*from: code.length*/, $this->maxStack, $this->maxLocals);
            }
        }
        if (($this->exceptionCount > 0)) {
            $this->cw->newUTF8("Exceptions");
            $size += (8 + (2 * $this->exceptionCount));
        }
        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->cw->version & 0xFFFF)) < Opcodes::V1_5) || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                $this->cw->newUTF8("Synthetic");
                $size += 6;
            }
        }
        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            $this->cw->newUTF8("Deprecated");
            $size += 6;
        }
        if ((ClassReader::SIGNATURES && ($this->signature != null))) {
            $this->cw->newUTF8("Signature");
            $this->cw->newUTF8($this->signature);
            $size += 8;
        }
        if (($this->methodParameters != null)) {
            $this->cw->newUTF8("MethodParameters");
            $size += (7 + count($this->methodParameters) /*from: methodParameters.length*/);
        }
        if ((ClassReader::ANNOTATIONS && ($this->annd != null))) {
            $this->cw->newUTF8("AnnotationDefault");
            $size += (6 + count($this->annd) /*from: annd.length*/);
        }
        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            $this->cw->newUTF8("RuntimeVisibleAnnotations");
            $size += (8 + $this->anns->getSize());
        }
        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            $this->cw->newUTF8("RuntimeInvisibleAnnotations");
            $size += (8 + $this->ianns->getSize());
        }
        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            $this->cw->newUTF8("RuntimeVisibleTypeAnnotations");
            $size += (8 + $this->tanns->getSize());
        }
        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            $this->cw->newUTF8("RuntimeInvisibleTypeAnnotations");
            $size += (8 + $this->itanns->getSize());
        }
        if ((ClassReader::ANNOTATIONS && ($this->panns != null))) {
            $this->cw->newUTF8("RuntimeVisibleParameterAnnotations");
            $size += (7 + (2 * ((count($this->panns) /*from: panns.length*/ - $this->synthetics))));
            for ($i = (count($this->panns) /*from: panns.length*/ - 1); ($i >= $this->synthetics); --$i) {
                $size += ( (($this->panns[$i] == null)) ? 0 : $this->panns[$i]->getSize() );
            }
        }
        if ((ClassReader::ANNOTATIONS && ($this->ipanns != null))) {
            $this->cw->newUTF8("RuntimeInvisibleParameterAnnotations");
            $size += (7 + (2 * ((count($this->ipanns) /*from: ipanns.length*/ - $this->synthetics))));
            for ($i = (count($this->ipanns) /*from: ipanns.length*/ - 1); ($i >= $this->synthetics); --$i) {
                $size += ( (($this->ipanns[$i] == null)) ? 0 : $this->ipanns[$i]->getSize() );
            }
        }
        if (($this->attrs != null)) {
            $size += $this->attrs->getSize($this->cw, null, 0, -1, -1);
        }
        return $size;
    }
    public function put($out) // [final ByteVector out]
    {
        $FACTOR = ClassWriter::$TO_ACC_SYNTHETIC;
        $mask = (((self::$ACC_CONSTRUCTOR | Opcodes::ACC_DEPRECATED) | ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE) | (((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) / $FACTOR)));
        $out->putShort(($this->access & ~$mask))->putShort($this->name)->putShort($this->desc);
        if (($this->classReaderOffset != 0)) {
            $out->putByteArray($this->cw->cr->b, $this->classReaderOffset, $this->classReaderLength);
            return ;
        }
        $attributeCount = 0;
        if ((count($this->code) /*from: code.length*/ > 0)) {
            ++$attributeCount;
        }
        if (($this->exceptionCount > 0)) {
            ++$attributeCount;
        }
        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->cw->version & 0xFFFF)) < Opcodes::V1_5) || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                ++$attributeCount;
            }
        }
        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            ++$attributeCount;
        }
        if ((ClassReader::SIGNATURES && ($this->signature != null))) {
            ++$attributeCount;
        }
        if (($this->methodParameters != null)) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->annd != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->panns != null))) {
            ++$attributeCount;
        }
        if ((ClassReader::ANNOTATIONS && ($this->ipanns != null))) {
            ++$attributeCount;
        }
        if (($this->attrs != null)) {
            $attributeCount += $this->attrs->getCount();
        }
        $out->putShort($attributeCount);
        if ((count($this->code) /*from: code.length*/ > 0)) {
            $size = ((12 + count($this->code) /*from: code.length*/) + (8 * $this->handlerCount));
            if (($this->localVar != null)) {
                $size += (8 + count($this->localVar) /*from: localVar.length*/);
            }
            if (($this->localVarType != null)) {
                $size += (8 + count($this->localVarType) /*from: localVarType.length*/);
            }
            if (($this->lineNumber != null)) {
                $size += (8 + count($this->lineNumber) /*from: lineNumber.length*/);
            }
            if (($this->stackMap != null)) {
                $size += (8 + count($this->stackMap) /*from: stackMap.length*/);
            }
            if ((ClassReader::ANNOTATIONS && ($this->ctanns != null))) {
                $size += (8 + $this->ctanns->getSize());
            }
            if ((ClassReader::ANNOTATIONS && ($this->ictanns != null))) {
                $size += (8 + $this->ictanns->getSize());
            }
            if (($this->cattrs != null)) {
                $size += $this->cattrs->getSize($this->cw, $this->code->data, count($this->code) /*from: code.length*/, $this->maxStack, $this->maxLocals);
            }
            $out->putShort($this->cw->newUTF8("Code"))->putInt($size);
            $out->putShort($this->maxStack)->putShort($this->maxLocals);
            $out->putInt(count($this->code) /*from: code.length*/)->putByteArray($this->code->data, 0, count($this->code) /*from: code.length*/);
            $out->putShort($this->handlerCount);
            if (($this->handlerCount > 0)) {
                $h = $this->firstHandler;
                while (($h != null)) {
                    $out->putShort($h->start->position)->putShort($h->end->position)->putShort($h->handler->position)->putShort($h->type);
                    $h = $h->next;
                }
            }
            $attributeCount = 0;
            if (($this->localVar != null)) {
                ++$attributeCount;
            }
            if (($this->localVarType != null)) {
                ++$attributeCount;
            }
            if (($this->lineNumber != null)) {
                ++$attributeCount;
            }
            if (($this->stackMap != null)) {
                ++$attributeCount;
            }
            if ((ClassReader::ANNOTATIONS && ($this->ctanns != null))) {
                ++$attributeCount;
            }
            if ((ClassReader::ANNOTATIONS && ($this->ictanns != null))) {
                ++$attributeCount;
            }
            if (($this->cattrs != null)) {
                $attributeCount += $this->cattrs->getCount();
            }
            $out->putShort($attributeCount);
            if (($this->localVar != null)) {
                $out->putShort($this->cw->newUTF8("LocalVariableTable"));
                $out->putInt((count($this->localVar) /*from: localVar.length*/ + 2))->putShort($this->localVarCount);
                $out->putByteArray($this->localVar->data, 0, count($this->localVar) /*from: localVar.length*/);
            }
            if (($this->localVarType != null)) {
                $out->putShort($this->cw->newUTF8("LocalVariableTypeTable"));
                $out->putInt((count($this->localVarType) /*from: localVarType.length*/ + 2))->putShort($this->localVarTypeCount);
                $out->putByteArray($this->localVarType->data, 0, count($this->localVarType) /*from: localVarType.length*/);
            }
            if (($this->lineNumber != null)) {
                $out->putShort($this->cw->newUTF8("LineNumberTable"));
                $out->putInt((count($this->lineNumber) /*from: lineNumber.length*/ + 2))->putShort($this->lineNumberCount);
                $out->putByteArray($this->lineNumber->data, 0, count($this->lineNumber) /*from: lineNumber.length*/);
            }
            if (($this->stackMap != null)) {
                $zip = ((($this->cw->version & 0xFFFF)) >= Opcodes::V1_6);
                $out->putShort($this->cw->newUTF8(( ($zip) ? "StackMapTable" : "StackMap" )));
                $out->putInt((count($this->stackMap) /*from: stackMap.length*/ + 2))->putShort($this->frameCount);
                $out->putByteArray($this->stackMap->data, 0, count($this->stackMap) /*from: stackMap.length*/);
            }
            if ((ClassReader::ANNOTATIONS && ($this->ctanns != null))) {
                $out->putShort($this->cw->newUTF8("RuntimeVisibleTypeAnnotations"));
                $this->ctanns->put($out);
            }
            if ((ClassReader::ANNOTATIONS && ($this->ictanns != null))) {
                $out->putShort($this->cw->newUTF8("RuntimeInvisibleTypeAnnotations"));
                $this->ictanns->put($out);
            }
            if (($this->cattrs != null)) {
                $this->cattrs->put($this->cw, $this->code->data, count($this->code) /*from: code.length*/, $this->maxLocals, $this->maxStack, $out);
            }
        }
        if (($this->exceptionCount > 0)) {
            $out->putShort($this->cw->newUTF8("Exceptions"))->putInt(((2 * $this->exceptionCount) + 2));
            $out->putShort($this->exceptionCount);
            for ($i = 0; ($i < $this->exceptionCount); ++$i) {
                $out->putShort($this->exceptions[$i]);
            }
        }
        if (((($this->access & Opcodes::ACC_SYNTHETIC)) != 0)) {
            if ((((($this->cw->version & 0xFFFF)) < Opcodes::V1_5) || ((($this->access & ClassWriter::$ACC_SYNTHETIC_ATTRIBUTE)) != 0))) {
                $out->putShort($this->cw->newUTF8("Synthetic"))->putInt(0);
            }
        }
        if (((($this->access & Opcodes::ACC_DEPRECATED)) != 0)) {
            $out->putShort($this->cw->newUTF8("Deprecated"))->putInt(0);
        }
        if ((ClassReader::SIGNATURES && ($this->signature != null))) {
            $out->putShort($this->cw->newUTF8("Signature"))->putInt(2)->putShort($this->cw->newUTF8($this->signature));
        }
        if (($this->methodParameters != null)) {
            $out->putShort($this->cw->newUTF8("MethodParameters"));
            $out->putInt((count($this->methodParameters) /*from: methodParameters.length*/ + 1))->putByte($this->methodParametersCount);
            $out->putByteArray($this->methodParameters->data, 0, count($this->methodParameters) /*from: methodParameters.length*/);
        }
        if ((ClassReader::ANNOTATIONS && ($this->annd != null))) {
            $out->putShort($this->cw->newUTF8("AnnotationDefault"));
            $out->putInt(count($this->annd) /*from: annd.length*/);
            $out->putByteArray($this->annd->data, 0, count($this->annd) /*from: annd.length*/);
        }
        if ((ClassReader::ANNOTATIONS && ($this->anns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeVisibleAnnotations"));
            $this->anns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->ianns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeInvisibleAnnotations"));
            $this->ianns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->tanns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeVisibleTypeAnnotations"));
            $this->tanns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->itanns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeInvisibleTypeAnnotations"));
            $this->itanns->put($out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->panns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeVisibleParameterAnnotations"));
            $AnnotationWriter->put($this->panns, $this->synthetics, $out);
        }
        if ((ClassReader::ANNOTATIONS && ($this->ipanns != null))) {
            $out->putShort($this->cw->newUTF8("RuntimeInvisibleParameterAnnotations"));
            $AnnotationWriter->put($this->ipanns, $this->synthetics, $out);
        }
        if (($this->attrs != null)) {
            $this->attrs->put($this->cw, null, 0, -1, -1, $out);
        }
    }
}
MethodWriter::__staticinit(); // initialize static vars for this class on load
