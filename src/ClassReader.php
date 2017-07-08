<?php

namespace Kambo\Asm;

class ClassReader
{
    /**
     * True to enable signatures support.
     */
    const SIGNATURES = true;

    /**
     * True to enable annotations support.
     */
    const ANNOTATIONS = true;

    /**
     * True to enable stack map frames support.
     */
    const FRAMES = true;

    /**
     * True to enable bytecode writing support.
     */
    const WRITER = true;

    /**
     * True to enable JSR_W and GOTO_W support.
     */
    const RESIZE = true;

    /**
     * Flag to skip method code. If this class is set <code>CODE</code>
     * attribute won't be visited. This can be used, for example, to retrieve
     * annotations for methods and method parameters.
     */
    const SKIP_CODE = 1;

    /**
     * Flag to skip the debug information in the class. If this flag is set the
     * debug information of the class is not visited, i.e. the
     * {@link MethodVisitor#visitLocalVariable visitLocalVariable} and
     * {@link MethodVisitor#visitLineNumber visitLineNumber} methods will not be
     * called.
     */
    const SKIP_DEBUG = 2;

    /**
     * Flag to skip the stack map frames in the class. If this flag is set the
     * stack map frames of the class is not visited, i.e. the
     * {@link MethodVisitor#visitFrame visitFrame} method will not be called.
     * This flag is useful when the {@link ClassWriter#COMPUTE_FRAMES} option is
     * used: it avoids visiting frames that will be ignored and recomputed from
     * scratch in the class writer.
     */
    const SKIP_FRAMES = 4;

    /**
     * Flag to expand the stack map frames. By default stack map frames are
     * visited in their original format (i.e. "expanded" for classes whose
     * version is less than V1_6, and "compressed" for the other classes). If
     * this flag is set, stack map frames are always visited in expanded format
     * (this option adds a decompression/recompression step in ClassReader and
     * ClassWriter which degrades performances quite a lot).
     */
    const EXPAND_FRAMES = 8;

    /**
     * Flag to expand the ASM pseudo instructions into an equivalent sequence of
     * standard bytecode instructions. When resolving a forward jump it may
     * happen that the signed 2 bytes offset reserved for it is not sufficient
     * to store the bytecode offset. In this case the jump instruction is
     * replaced with a temporary ASM pseudo instruction using an unsigned 2
     * bytes offset (see Label#resolve). This internal flag is used to re-read
     * classes containing such instructions, in order to replace them with
     * standard instructions. In addition, when this flag is used, GOTO_W and
     * JSR_W are <i>not</i> converted into GOTO and JSR, to make sure that
     * infinite loops where a GOTO_W is replaced with a GOTO in ClassReader and
     * converted back to a GOTO_W in ClassWriter cannot occur.
     */
    const EXPAND_ASM_INSNS = 256;
}
