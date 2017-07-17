<?php

namespace Kambo\Asm;

/**
 * A visitor to visit a Java class. The methods of this class must be called in
 * the following order: <tt>visit</tt> [ <tt>visitSource</tt> ] [
 * <tt>visitOuterClass</tt> ] ( <tt>visitAnnotation</tt> |
 * <tt>visitTypeAnnotation</tt> | <tt>visitAttribute</tt> )* (
 * <tt>visitInnerClass</tt> | <tt>visitField</tt> | <tt>visitMethod</tt> )*
 * <tt>visitEnd</tt>.
 *
 * @author Eric Bruneton
 */
/*abstract*/ class ClassVisitor
{
    protected $api; // int
    protected $cv;  // ClassVisitor

    /**
     * Constructs a new {@link ClassVisitor}.
     *
     * @param api
     *            the ASM API version implemented by this visitor. Must be one
     *            of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     */
    public static function constructor__I($api) // [final int api]
    {
        $me = new self();
        self::constructor__I_ClassVisitor($api, null);
        return $me;
    }

    /**
     * Constructs a new {@link ClassVisitor}.
     *
     * @param api
     *            the ASM API version implemented by this visitor. Must be one
     *            of {@link Opcodes#ASM4} or {@link Opcodes#ASM5}.
     * @param cv
     *            the class visitor to which this visitor must delegate method
     *            calls. May be null.
     */
    public static function constructor__I_ClassVisitor($api, $cv) // [final int api, final ClassVisitor cv]
    {
        $me = new self();
        if ((($api != Opcodes::ASM4) && ($api != Opcodes::ASM5))) {
            throw new IllegalArgumentException();
        }
        $me->api = $api;
        $me->cv = $cv;
        return $me;
    }

    /**
     * Visits the header of the class.
     *
     * @param version
     *            the class version.
     * @param access
     *            the class's access flags (see {@link Opcodes}). This parameter
     *            also indicates if the class is deprecated.
     * @param name
     *            the internal name of the class (see
     *            {@link Type#getInternalName() getInternalName}).
     * @param signature
     *            the signature of this class. May be <tt>null</tt> if the class
     *            is not a generic one, and does not extend or implement generic
     *            classes or interfaces.
     * @param superName
     *            the internal of name of the super class (see
     *            {@link Type#getInternalName() getInternalName}). For
     *            interfaces, the super class is {@link Object}. May be
     *            <tt>null</tt>, but only for the {@link Object} class.
     * @param interfaces
     *            the internal names of the class's interfaces (see
     *            {@link Type#getInternalName() getInternalName}). May be
     *            <tt>null</tt>.
     */
    public function visit(int $version, int $access, string $name, string $signature = null, string $superName = null, array $interfaces=null)
    {
        if (($this->cv != null)) {
            $this->cv->visit($version, $access, $name, $signature, $superName, $interfaces);
        }
    }

    /**
     * Visits the source of the class.
     *
     * @param source
     *            the name of the source file from which the class was compiled.
     *            May be <tt>null</tt>.
     * @param debug
     *            additional debug information to compute the correspondance
     *            between source and compiled elements of the class. May be
     *            <tt>null</tt>.
     */
    public function visitSource(string $source, string $debug)
    {
        if (($this->cv != null)) {
            $this->cv->visitSource($source, $debug);
        }
    }

    /**
     * Visits the enclosing class of the class. This method must be called only
     * if the class has an enclosing class.
     *
     * @param owner
     *            internal name of the enclosing class of the class.
     * @param name
     *            the name of the method that contains the class, or
     *            <tt>null</tt> if the class is not enclosed in a method of its
     *            enclosing class.
     * @param desc
     *            the descriptor of the method that contains the class, or
     *            <tt>null</tt> if the class is not enclosed in a method of its
     *            enclosing class.
     */
    public function visitOuterClass(string $owner, string $name, string $desc) // [String owner, String name, String desc]
    {
        if (($this->cv != null)) {
            $this->cv->visitOuterClass($owner, $name, $desc);
        }
    }

    /**
     * Visits an annotation of the class.
     *
     * @param desc
     *            the class descriptor of the annotation class.
     * @param visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     *
     * @return a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     */
    public function visitAnnotation(string $desc, bool $visible)
    {
        if (($this->cv != null)) {
            return $this->cv->visitAnnotation($desc, $visible);
        }
        return null;
    }

    /**
     * Visits an annotation on a type in the class signature.
     *
     * @param typeRef
     *            a reference to the annotated type. The sort of this type
     *            reference must be {@link TypeReference#CLASS_TYPE_PARAMETER
     *            CLASS_TYPE_PARAMETER},
     *            {@link TypeReference#CLASS_TYPE_PARAMETER_BOUND
     *            CLASS_TYPE_PARAMETER_BOUND} or
     *            {@link TypeReference#CLASS_EXTENDS CLASS_EXTENDS}. See
     *            {@link TypeReference}.
     * @param typePath
     *            the path to the annotated type argument, wildcard bound, array
     *            element type, or static inner type within 'typeRef'. May be
     *            <tt>null</tt> if the annotation targets 'typeRef' as a whole.
     * @param desc
     *            the class descriptor of the annotation class.
     * @param visible
     *            <tt>true</tt> if the annotation is visible at runtime.
     * @return a visitor to visit the annotation values, or <tt>null</tt> if
     *         this visitor is not interested in visiting this annotation.
     */
    public function visitTypeAnnotation(int $typeRef, $typePath, string $desc, bool $visible) // [int typeRef, TypePath typePath, String desc, boolean visible]
    {
        if (($this->api < Opcodes::ASM5)) {
            throw new RuntimeException();
        }
        if (($this->cv != null)) {
            return $this->cv->visitTypeAnnotation($typeRef, $typePath, $desc, $visible);
        }
        return null;
    }

    /**
     * Visits a non standard attribute of the class.
     *
     * @param attr
     *            an attribute.
     */
    public function visitAttribute($attr) // [Attribute attr]
    {
        if (($this->cv != null)) {
            $this->cv->visitAttribute($attr);
        }
    }

    /**
     * Visits information about an inner class. This inner class is not
     * necessarily a member of the class being visited.
     *
     * @param name
     *            the internal name of an inner class (see
     *            {@link Type#getInternalName() getInternalName}).
     * @param outerName
     *            the internal name of the class to which the inner class
     *            belongs (see {@link Type#getInternalName() getInternalName}).
     *            May be <tt>null</tt> for not member classes.
     * @param innerName
     *            the (simple) name of the inner class inside its enclosing
     *            class. May be <tt>null</tt> for anonymous inner classes.
     * @param access
     *            the access flags of the inner class as originally declared in
     *            the enclosing class.
     */
    public function visitInnerClass(string $name, string $outerName, string $innerName, int $access) // [String name, String outerName, String innerName, int access]
    {
        if (($this->cv != null)) {
            $this->cv->visitInnerClass($name, $outerName, $innerName, $access);
        }
    }

    public function visitField(int $access, string $name, string $desc, string $signature = null, $value = null) // [int access, String name, String desc, String signature, Object value]
    {
        if (($this->cv != null)) {
            return $this->cv->visitField($access, $name, $desc, $signature, $value);
        }
        return null;
    }

    public function visitMethod(int $access, string $name, string $desc, string $signature = null, array $exceptions = null) // [int access, String name, String desc, String signature, String[] exceptions]
    {
        if (($this->cv != null)) {
            return $this->cv->visitMethod($access, $name, $desc, $signature, $exceptions);
        }
        return null;
    }

    public function visitEnd()
    {
        if (($this->cv != null)) {
            $this->cv->visitEnd();
        }
    }
}
