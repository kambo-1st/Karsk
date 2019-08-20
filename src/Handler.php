<?php
/**
 * Karsk - write java bytecode in PHP!
 * Copyright (c) 2018, Bohuslav Šimek
 * Based on ASM: a very small and fast Java bytecode manipulation framework
 * Copyright (c) 2000-2011 INRIA, France Telecom
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. Neither the name of the copyright holders nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Kambo\Karsk;

class Handler
{
    /**
     * Beginning of the exception handler's scope (inclusive).
     *
     * @var Label
     */
    public $start;

    /**
     * End of the exception handler's scope (exclusive).
     *
     * @var Label
     */
    public $end;

    /**
     * Beginning of the exception handler's code.
     *
     * @var Label
     */
    public $handler;

    /**
     * Internal name of the type of exceptions handled by this handler, or
     * <tt>null</tt> to catch any exceptions.
     *
     * @var string
     */
    public $desc;

    /**
     * Constant pool index of the internal name of the type of exceptions
     * handled by this handler, or 0 to catch any exceptions.
     *
     * @var int
     */
    public $type;

    /**
     * Next exception handler block info.
     *
     * @var Handler
     */
    public $next;

    /**
     * Removes the range between start and end from the given exception
     * handlers.
     *
     * @param Handler $h     an exception handler list.
     * @param Label   $start the start of the range to be removed.
     * @param Label   $end   the end of the range to be removed. Maybe null.
     *
     * @return self the exception handler list with the start-end range removed.
     */
    public static function remove(Handler $h, $start, $end)
    {
        if ($h == null) {
            return null;
        } else {
            $h->next = Handler::remove($h->next, $start, $end);
        }

        $hstart = $h->start->position;
        $hend   = $h->end->position;
        $s      = $start->position;

        // TODO create constant for Integer.MAX_VALUE =  2147483647
        $e = (($end == null) ? 2147483647 : $end->position);
        // if [hstart,hend[ and [s,e[ intervals intersect...
        if (($s < $hend) && ($e > $hstart)) {
            if ($s <= $hstart) {
                if ($e >= $hend) {
                    // [hstart,hend[ fully included in [s,e[, h removed
                    $h = $h->next;
                } else {
                    // [hstart,hend[ minus [s,e[ = [e,hend[
                    $h->start = $end;
                }
            } elseif ($e >= $hend) {
                // [hstart,hend[ minus [s,e[ = [hstart,s[
                $h->end = $start;
            } else {
                // [hstart,hend[ minus [s,e[ = [hstart,s[ + [e,hend[
                $g = new Handler();
                $g->start = $end;
                $g->end = $h->end;
                $g->handler = $h->handler;
                $g->desc = $h->desc;
                $g->type = $h->type;
                $g->next = $h->next;
                $h->end = $start;
                $h->next = $g;
            }
        }

        return $h;
    }
}
